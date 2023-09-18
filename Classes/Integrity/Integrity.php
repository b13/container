<?php

declare(strict_types=1);

namespace B13\Container\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Integrity\Error\ChildInTranslatedContainerError;
use B13\Container\Integrity\Error\NonExistingParentWarning;
use B13\Container\Integrity\Error\UnusedColPosWarning;
use B13\Container\Integrity\Error\WrongL18nParentError;
use B13\Container\Integrity\Error\WrongLanguageWarning;
use B13\Container\Integrity\Error\WrongParentError;
use B13\Container\Integrity\Error\WrongPidError;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\SingletonInterface;

class Integrity implements SingletonInterface
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var string[][]
     */
    protected $res = [
        'errors' => [],
        'warnings' => [],
    ];

    public function __construct(Database $database, Registry $tcaRegistry)
    {
        $this->database = $database;
        $this->tcaRegistry = $tcaRegistry;
    }

    public function run(): array
    {
        $cTypes = $this->tcaRegistry->getRegisteredCTypes();
        $colPosByCType = [];
        foreach ($cTypes as $cType) {
            $columns = $this->tcaRegistry->getAvailableColumns($cType);
            $colPosByCType[$cType] = [];
            foreach ($columns as $column) {
                $colPosByCType[$cType][] = $column['colPos'];
            }
        }
        $this->defaultLanguageRecords($cTypes, $colPosByCType);
        $this->nonDefaultLanguageRecords($cTypes, $colPosByCType);
        return $this->res;
    }

    private function nonDefaultLanguageRecords(array $cTypes, array $colPosByCType): void
    {
        $nonDefaultLanguageChildRecords = $this->database->getNonDefaultLanguageContainerChildRecords();
        $nonDefaultLanguageContainerRecords = $this->database->getNonDefaultLanguageContainerRecords($cTypes);
        foreach ($nonDefaultLanguageContainerRecords as $containerRecord) {
            if ($containerRecord['uid'] === $containerRecord['tx_container_parent']) {
                $this->res['errors'][] = new WrongParentError($containerRecord);
            }
        }
        $defaultLanguageContainerRecords = $this->database->getContainerRecords($cTypes);
        foreach ($nonDefaultLanguageChildRecords as $nonDefaultLanguageChildRecord) {
            if ($nonDefaultLanguageChildRecord['l18n_parent'] > 0) {
                // connected mode
                // tx_container_parent should be default container record uid
                if (!isset($defaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']])) {
                    if (isset($nonDefaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']])) {
                        $containerRecord = $nonDefaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']];
                        if ($containerRecord['sys_language_uid'] === $nonDefaultLanguageChildRecord['sys_language_uid'] && $containerRecord['l18n_parent'] > 0) {
                            $this->res['errors'][] = new ChildInTranslatedContainerError($nonDefaultLanguageChildRecord, $containerRecord);
                        } else {
                            $this->res['warnings'][] = new NonExistingParentWarning($nonDefaultLanguageChildRecord);
                        }
                    } else {
                        $this->res['warnings'][] = new NonExistingParentWarning($nonDefaultLanguageChildRecord);
                    }
                } elseif (isset($nonDefaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']])) {
                    $containerRecord = $nonDefaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']];
                    $this->res['errors'][] = new WrongL18nParentError($nonDefaultLanguageChildRecord, $containerRecord);
                }
            } else {
                // free mode, can be created direct, or by copyToLanguage
                // tx_container_parent should be nonDefaultLanguage container record uid
                if (isset($defaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']])) {
                    $containerRecord = $defaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']];
                    if ($containerRecord['pid'] !== $nonDefaultLanguageChildRecord['pid']) {
                        $this->res['errors'][] = new WrongPidError($nonDefaultLanguageChildRecord, $containerRecord);
                    }
                    $this->res['warnings'][] = new WrongLanguageWarning($nonDefaultLanguageChildRecord, $containerRecord);
                } elseif (!isset($nonDefaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']])) {
                    $this->res['warnings'][] = new NonExistingParentWarning($nonDefaultLanguageChildRecord);
                } else {
                    $containerRecord = $nonDefaultLanguageContainerRecords[$nonDefaultLanguageChildRecord['tx_container_parent']];
                    if ($containerRecord['pid'] !== $nonDefaultLanguageChildRecord['pid']) {
                        $this->res['errors'][] = new WrongPidError($nonDefaultLanguageChildRecord, $containerRecord);
                    }
                    if ($containerRecord['sys_language_uid'] !== $nonDefaultLanguageChildRecord['sys_language_uid']) {
                        $this->res['errors'][] = new WrongL18nParentError($nonDefaultLanguageChildRecord, $containerRecord);
                    }
                    if (!in_array($nonDefaultLanguageChildRecord['colPos'], $colPosByCType[$containerRecord['CType']])) {
                        $this->res['warnings'][] = new UnusedColPosWarning($nonDefaultLanguageChildRecord, $containerRecord);
                    }
                    if ($containerRecord['l18n_parent'] > 0) {
                        $this->res['errors'][] = new WrongL18nParentError($nonDefaultLanguageChildRecord, $containerRecord);
                    }
                }
            }
        }
    }

    private function defaultLanguageRecords(array $cTypes, array $colPosByCType): void
    {
        $containerRecords = $this->database->getContainerRecords($cTypes);
        foreach ($containerRecords as $containerRecord) {
            if ($containerRecord['uid'] === $containerRecord['tx_container_parent']) {
                $this->res['errors'][] = new WrongParentError($containerRecord);
            }
        }
        $containerChildRecords = $this->database->getContainerChildRecords();
        foreach ($containerChildRecords as $containerChildRecord) {
            if (!isset($containerRecords[$containerChildRecord['tx_container_parent']])) {
                // can happen when container CType is changed
                $this->res['warnings'][] = new NonExistingParentWarning($containerChildRecord);
            } else {
                $containerRecord = $containerRecords[$containerChildRecord['tx_container_parent']];
                if ($containerRecord['pid'] !== $containerChildRecord['pid']) {
                    $this->res['errors'][] = new WrongPidError($containerChildRecord, $containerRecord);
                }
                if (!in_array($containerChildRecord['colPos'], $colPosByCType[$containerRecord['CType']])) {
                    // can happen when container CType is changed
                    $this->res['warnings'][] = new UnusedColPosWarning($containerChildRecord, $containerRecord);
                }
            }
        }
    }
}
