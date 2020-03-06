<?php

namespace B13\Container\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\Registry;

class ContainerFactory implements SingletonInterface
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
     * ContainerFactory constructor.
     * @param Database|null $database
     * @param Registry|null $tcaRegistry
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @param int $uid
     * @return Container
     */
    public function buildContainer(int $uid): Container
    {

        // FE $uid alays default language uid
        // BE $uid localized $uid
        if (TYPO3_MODE === 'FE') {
            $languageAspect =  GeneralUtility::makeInstance(Context::class)->getAspect('language');
            $language = $languageAspect->get('id');
            if ($language > 0) {
                return $this->buildContainerWithOverlay($uid, $languageAspect);
            }
        }

        $record = $this->database->fetchOneRecord($uid);
        if ($record === null) {
            throw new Exception('cannot fetch record with uid ' . $uid, 1576572850);
        }
        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
            throw new Exception('not a container element with uid ' . $uid, 1576572851);
        }

        $defaultRecord = null;
        $language = (int)$record['sys_language_uid'];
        if ($language > 0) {
            $defaultRecord = $this->database->fetchOneDefaultRecord($record);
            if ($defaultRecord === null) {
                // free mode
                $childRecords = $this->database->fetchRecordsByParentAndLanguage($record['uid'], $language);
            } else {
                // connected mode
                $defaultRecords = $this->database->fetchRecordsByParentAndLanguage($defaultRecord['uid'], 0);
                $localizedRecords = $this->database->fetchOverlayRecords($defaultRecords, $language);
                $childRecords = $this->sortLocalizedRecordsByDefaultRecords($defaultRecords, $localizedRecords);
            }
        } else {
            $childRecords = $this->database->fetchRecordsByParentAndLanguage($record['uid'], $language);
        }
        $childRecords = $this->doWorkspaceOverlay($childRecords);
        $childRecordByColPosKey = $this->recordsByColPosKey($childRecords);
        if ($defaultRecord === null) {
            $container = new Container($record, $childRecordByColPosKey, $language);
        } else {
            $container = new Container($defaultRecord, $childRecordByColPosKey, $language);
        }
        return $container;
    }

    /**
     * @param array $defaultRecords
     * @return array
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function doWorkspaceOverlay(array $defaultRecords): array
    {
        $workspaceId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id');
        if ($workspaceId > 0) {
            $workspaceRecords = $this->database->fetchWorkspaceRecords($defaultRecords, $workspaceId);
            $overlayed = [];
            foreach ($defaultRecords as $defaultRecord) {
                $foundOverlay = null;
                foreach ($workspaceRecords as $workspaceRecord) {
                    if($workspaceRecord['t3ver_oid'] === $defaultRecord['uid']) {
                        $foundOverlay = $workspaceRecord;
                    }
                }
                if ($foundOverlay !== null) {
                    $overlayed[] = $foundOverlay;
                } else {
                    $overlayed[] = $defaultRecord;
                }
            }
            return $overlayed;
        } else {
            // filter workspace placeholders
            $filtered = [];
            foreach($defaultRecords as $defaultRecord) {
                if ($defaultRecord['t3ver_wsid'] === 0) {
                    $filtered[] = $defaultRecord;
                }
            }
            return $filtered;
        }
    }

    /**
     * @param int $uid
     * @return Container
     */
    protected function buildContainerWithOverlay(int $uid, LanguageAspect $languageAspect): Container
    {
        $language = $languageAspect->get('id');
        $record = $this->database->fetchOneOverlayRecord($uid, $language);
        if ($record === null) {
            if ($languageAspect->doOverlays()) {
                $record = $this->database->fetchOneRecord($uid);
            }
        }

        if ($record === null) {
            throw new Exception('cannot fetch record with uid ' . $uid, 1576572852);
        }
        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
            throw new Exception('not a container element with uid ' . $uid, 1576572853);
        }

        $defaultRecord = null;
        if ($record['sys_language_uid'] > 0) {
            $defaultRecord = $this->database->fetchOneDefaultRecord($record);
            if ($defaultRecord === null) {
                // free mode
                $childRecords = $this->database->fetchRecordsByParentAndLanguage($record['uid'], $language);
            } else {
                // connected mode
                $childRecords = $this->database->fetchRecordsByParentAndLanguage($defaultRecord['uid'], 0);
                if ($languageAspect->doOverlays()) {
                    $childRecordsOverlays = $this->database->fetchOverlayRecords($childRecords, $language);
                    $childRecords = $this->doOverlay($childRecords, $childRecordsOverlays);
                }
            }
        } else {
            $childRecords = $this->database->fetchRecordsByParentAndLanguage($record['uid'], 0);
            if ($languageAspect->doOverlays()) {
                $childRecordsOverlays = $this->database->fetchOverlayRecords($childRecords, $language);
                $childRecords = $this->doOverlay($childRecords, $childRecordsOverlays);
            }
        }

        $childRecords = $this->doWorkspaceOverlay($childRecords);
        $childRecordByColPosKey = $this->recordsByColPosKey($childRecords);
        if ($defaultRecord === null) {
            $container = new Container($record, $childRecordByColPosKey, $language);
        } else {
            $container = new Container($defaultRecord, $childRecordByColPosKey, $language);
        }
        return $container;
    }

    /**
     * @param array $defaultRecords
     * @param array $localizedRecords
     * @return array
     */
    protected function sortLocalizedRecordsByDefaultRecords(array $defaultRecords, array $localizedRecords): array
    {
        $sorted = [];
        foreach ($defaultRecords as $defaultRecord) {
            foreach ($localizedRecords as $localizedRecord) {
                if ($localizedRecord['l18n_parent'] === $defaultRecord['uid']) {
                    $sorted[] = $localizedRecord;
                }
            }
        }
        return $sorted;
    }

    /**
     * @param array $defaultRecords
     * @param array $localizedRecords
     * @return array
     */
    protected function doOverlay(array $defaultRecords, array $localizedRecords): array
    {
        $overlayed = [];
        foreach ($defaultRecords as $defaultRecord) {
            $foundOverlay = null;
            foreach ($localizedRecords as $localizedRecord) {
                if($localizedRecord['l18n_parent'] === $defaultRecord['uid']) {
                    $foundOverlay = $localizedRecord;
                }
            }
            if ($foundOverlay !== null) {
                $overlayed[] = $foundOverlay;
            } else {
                $overlayed[] = $defaultRecord;
            }
        }
        return $overlayed;
    }

    /**
     * @param array $records
     * @return array
     */
    protected function recordsByColPosKey(array $records): array
    {
        $recordsByColPosKey = [];
        foreach ($records as $record) {
            if (empty($recordsByColPosKey[$record['colPos']])) {
                $recordsByColPosKey[$record['colPos']] = [];
            }
            $recordsByColPosKey[$record['colPos']][] = $record;
        }
        return $recordsByColPosKey;
    }

}
