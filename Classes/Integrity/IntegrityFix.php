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
use B13\Container\Integrity\Error\WrongL18nParentError;
use B13\Container\Integrity\Error\WrongPidError;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IntegrityFix implements SingletonInterface
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(Database $database, Registry $tcaRegistry)
    {
        $this->database = $database;
        $this->tcaRegistry = $tcaRegistry;
    }

    public function deleteChildrenWithWrongPid(WrongPidError $wrongPidError): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = false;
        $childRecord = $wrongPidError->getChildRecord();
        $cmd = ['tt_content' => [$childRecord['uid'] => ['delete' => 1]]];
        $dataHandler->start([], $cmd);
        $dataHandler->process_cmdmap();
    }

    public function deleteChildrenWithNonExistingParent(NonExistingParentWarning $nonExistingParentWarning): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = false;
        $childRecord = $nonExistingParentWarning->getChildRecord();
        $cmd = ['tt_content' => [$childRecord['uid'] => ['delete' => 1]]];
        $dataHandler->start([], $cmd);
        $dataHandler->process_cmdmap();
    }

    public function changeContainerParentToDefaultLanguageContainer(ChildInTranslatedContainerError $e): void
    {
        $translatedContainer = $e->getContainerRecord();
        $child = $e->getChildRecord();
        $l18nParentOfContainer = $translatedContainer['l18n_parent'];
        $queryBuilder = $this->database->getQueryBuilder();
        $queryBuilder->update('tt_content')
            ->set('tx_container_parent', $l18nParentOfContainer)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($child['uid'], \PDO::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $queryBuilder->executeStatement();
        } else {
            $queryBuilder->execute();
        }
    }

    /**
     * @param WrongL18nParentError[] $errors
     */
    public function languageMode(array $errors): void
    {
        $cTypes = $this->tcaRegistry->getRegisteredCTypes();
        $defaultContainerRecords = $this->database->getContainerRecords($cTypes);
        $containerRecords = [];
        // uniq container records
        foreach ($errors as $error) {
            $containerRecord = $error->getContainerRecord();
            $containerRecords[$containerRecord['uid']] = $containerRecord;
        }
        foreach ($containerRecords as $containerRecord) {
            if (!isset($defaultContainerRecords[$containerRecord['l18n_parent']])) {
                // should not happen
                continue;
            }
            $defaultContainerRecord = $defaultContainerRecords[$containerRecord['l18n_parent']];
            $columns = $this->tcaRegistry->getAvailableColumns($defaultContainerRecord['CType']);
            foreach ($columns as $column) {
                $childRecords = $this->database->getChildrenByContainerAndColPos($containerRecord['uid'], (int)$column['colPos'], $containerRecord['sys_language_uid']);
                // some children may have corrent container parent set
                //$childRecords = array_merge($childRecords, $this->database->getChildrenByContainer($defaultContainerRecord['uid'], $containerRecord['sys_language_uid']));
                $defaultChildRecords = $this->database->getChildrenByContainerAndColPos($defaultContainerRecord['uid'], (int)$column['colPos'], $defaultContainerRecord['sys_language_uid']);
                if (count($childRecords) <= count($defaultChildRecords)) {
                    // connect children
                    for ($i = 0; $i < count($childRecords); $i++) {
                        $childRecord = $childRecords[$i];
                        $defaultChildRecord = $defaultChildRecords[$i];
                        $queryBuilder = $this->database->getQueryBuilder();
                        $stm = $queryBuilder->update('tt_content')
                            ->set('tx_container_parent', $defaultContainerRecord['uid'])
                            ->set('l18n_parent', $defaultChildRecord['uid'])
                            ->where(
                                $queryBuilder->expr()->eq(
                                    'uid',
                                    $queryBuilder->createNamedParameter($childRecord['uid'], \PDO::PARAM_INT)
                                )
                            );
                        if ((int)$childRecord['l10n_source'] === 0) {
                            // i think this is always true
                            $stm->set('l10n_source', $defaultChildRecord['uid']);
                        }
                        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
                            $stm = $stm->executeStatement();
                        } else {
                            $stm = $stm->execute();
                        }
                    }
                }
                // disconnect container ?
            }
        }
    }
}
