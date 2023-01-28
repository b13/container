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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerFactory extends \B13\Container\Domain\Factory\PageView\ContainerFactory
{
    /**
     * @var ContentStorage
     */
    protected $contentStorage;

    public function __construct(
        Database $database,
        Registry $tcaRegistry,
        Context $context,
        ContentStorage $contentStorage
    ) {
        parent::__construct($database, $tcaRegistry, $context);
        $this->contentStorage = $contentStorage;
    }

    public function buildContainer(int $uid): Container
    {
        /** @var LanguageAspect $languageAspect */
        $languageAspect =  GeneralUtility::makeInstance(Context::class)->getAspect('language');
        $language = $languageAspect->getId();
        if ($language > 0 && $languageAspect->doOverlays()) {
            return $this->buildContainerWithOverlay($uid, $languageAspect);
        }
        return parent::buildContainer($uid);
    }

    protected function doOverlay(array $defaultRecords, LanguageAspect $languageAspect): array
    {
        $typo3Version = new Typo3Version();
        $overlayed = [];
        $pageRepository = $this->contentStorage->getPageRepository();
        foreach ($defaultRecords as $defaultRecord) {
            if ($typo3Version->getMajorVersion() < 12) {
                $overlay = $pageRepository->getRecordOverlay(
                    'tt_content',
                    $defaultRecord,
                    $languageAspect->getContentId(),
                    $languageAspect->getOverlayType() === $languageAspect::OVERLAYS_MIXED ? '1' : 'hideNonTranslated'
                );
            } else {
                $overlay = $pageRepository->getLanguageOverlay(
                    'tt_content',
                    $defaultRecord,
                    $languageAspect
                );
            }
            if ($overlay !== null) {
                $overlayed[] = $overlay;
            }
        }
        return $overlayed;
    }

    protected function buildContainerWithOverlay(int $uid, LanguageAspect $languageAspect): Container
    {
        $language = $languageAspect->get('id');
        $record = $this->database->fetchOneOverlayRecord($uid, $language);
        if ($record === null) {
            $record = $this->database->fetchOneRecord($uid);
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
                $childRecords = $this->children($record, (int)$record['sys_language_uid']);
            } else {
                // connected mode
                $childRecords = $this->children($defaultRecord, 0);
                $childRecords = $this->doOverlay($childRecords, $languageAspect);
            }
        } else {
            // container record with sys_language_uid=0
            $childRecords = $this->children($record, 0);
            $childRecords = $this->doOverlay($childRecords, $languageAspect);
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
