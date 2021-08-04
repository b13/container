<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory\PageView\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Model\Container;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerFactory extends \B13\Container\Domain\Factory\PageView\ContainerFactory
{
    /**
     * @var ContentStorage
     */
    protected $contentStorage;

    public function __construct(
        Database $database = null,
        Registry $tcaRegistry = null,
        ContentStorage $contentStorage = null
    ) {
        parent::__construct($database, $tcaRegistry);
        $this->contentStorage = $contentStorage ?? GeneralUtility::makeInstance(ContentStorage::class);
    }

    /**
     * @param int $uid
     * @return Container
     */
    public function buildContainer(int $uid): Container
    {
        #$languageAspect =  GeneralUtility::makeInstance(Context::class)->getAspect('language');
        #$language = $languageAspect->get('id');
        #if ($language > 0 && $languageAspect->doOverlays()) {
        #    return $this->buildContainerWithOverlay($uid, $languageAspect);
        #}
        return parent::buildContainer($uid);
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
                if ($localizedRecord['l18n_parent'] === $defaultRecord['uid'] ||
                    $localizedRecord['l18n_parent'] === $defaultRecord['t3ver_oid']
                ) {
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
     * @param int $uid
     * @param LanguageAspect $languageAspect
     * @return Container
     * @throws Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
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
            $defaultRecord = $this->defaultContainer($record);
            if ($defaultRecord === null) {
                // free mode
                $childRecords = $this->children($record, $language);
            } else {
                // connected mode
                $childRecords = $this->children($defaultRecord, 0);
                if ($languageAspect->doOverlays()) {
                    $childRecordsOverlays = $this->localizedRecordsByDefaultRecords($childRecords, $language);
                    $childRecords = $this->doOverlay($childRecords, $childRecordsOverlays);
                }
            }
        } else {
            // container record with sys_language_uid=0
            $childRecords = $this->children($record, 0);
            if ($languageAspect->doOverlays()) {
                $childRecordsOverlays = $this->localizedRecordsByDefaultRecords($childRecords, $language);
                $childRecords = $this->doOverlay($childRecords, $childRecordsOverlays);
            }
        }
        $childRecordByColPosKey = $this->recordsByColPosKey($childRecords);
        if ($defaultRecord === null) {
            $container = GeneralUtility::makeInstance(Container::class, $record, $childRecordByColPosKey, $language);
        } else {
            $container = GeneralUtility::makeInstance(Container::class, $defaultRecord, $childRecordByColPosKey, $language);
        }
        return $container;
    }
}
