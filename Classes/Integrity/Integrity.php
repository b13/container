<?php

namespace B13\Container\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Integrity\Error\NonExistingParentError;
use B13\Container\Integrity\Error\UnusedColPosWarning;
use B13\Container\Integrity\Error\WrongL18nParentError;
use B13\Container\Integrity\Error\WrongPidError;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Integrity implements SingletonInterface
{

    /**
     * @var Database
     */
    protected $database = null;

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    /**
     * @var string[][]
     */
    protected $res = [
        'errors' => [],
        'warnings' => []
    ];


    /**
     * ContainerFactory constructor.
     * @param Database|null $database
     * @param Registry|null $tcaRegistry
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
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
        $this->localizedRecords($cTypes, $colPosByCType);
        return $this->res;
    }

    /**
     * @param array $cTypes
     * @param array $colPosByCType
     */
    private function localizedRecords(array $cTypes, array $colPosByCType): void
    {
        $containerChildRecords = $this->database->getTranslatedContainerChildRecords();
        $containerRecords = $this->database->getTranslatedContainerRecords($cTypes);
        foreach ($containerChildRecords as $containerChildRecord) {
            $containerRecord = $containerRecords[$containerChildRecord['tx_container_parent']];
            if (
                ($containerRecord['l18n_parent'] === 0 && $containerChildRecord['l18n_parent'] !== 0) ||
                ($containerRecord['l18n_parent'] !== 0 && $containerChildRecord['l18n_parent'] === 0)
            ) {
                $this->res['errors'][] = new WrongL18nParentError($containerChildRecord, $containerRecord);
            }
        }
    }

    /**
     * @param array $cTypes
     * @param array $colPosByCType
     */
    private function defaultLanguageRecords(array $cTypes, array $colPosByCType): void
    {
        $containerRecords = $this->database->getContainerRecords($cTypes);
        $containerChildRecords = $this->database->getContainerChildRecords();
        foreach ($containerChildRecords as $containerChildRecord) {
            if (empty($containerRecords[$containerChildRecord['tx_container_parent']])) {
                $this->res['errors'][] = new NonExistingParentError($containerChildRecord);
            } else {
                $containerRecord = $containerRecords[$containerChildRecord['tx_container_parent']];
                if ($containerRecord['pid'] !== $containerChildRecord['pid']) {
                    $this->res['errors'][] = new WrongPidError($containerChildRecord, $containerRecord);
                }
                if (!in_array($containerChildRecord['colPos'], $colPosByCType[$containerRecord['CType']])) {
                    $this->res['warnings'][] = new UnusedColPosWarning($containerChildRecord, $containerRecord);
                }
            }

        }
    }


}
