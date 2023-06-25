<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Model\Container;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class ContainerFactory implements SingletonInterface
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
     * @var int
     */
    protected $workspaceId = 0;

    public function __construct(Database $database, Registry $tcaRegistry, Context $context)
    {
        $this->database = $database;
        $this->tcaRegistry = $tcaRegistry;
        $this->workspaceId = (int)$context->getPropertyFromAspect('workspace', 'id');
    }

    protected function containerByUid(int $uid): ?array
    {
        return $this->database->fetchOneRecord($uid);
    }

    protected function defaultContainer(array $localizedContainer): ?array
    {
        return $this->database->fetchOneDefaultRecord($localizedContainer);
    }

    public function buildContainer(int $uid): Container
    {
        $record = $this->containerByUid($uid);
        if ($record === null) {
            throw new Exception('cannot fetch record with uid ' . $uid, 1576572850);
        }
        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
            throw new Exception('not a container element with uid ' . $uid, 1576572851);
        }

        $defaultRecord = null;
        $language = (int)$record['sys_language_uid'];
        if ($language > 0) {
            $defaultRecord = $this->defaultContainer($record);
            if ($defaultRecord === null) {
                // free mode
                $childRecords = $this->children($record, $language);
            } else {
                // connected mode
                $defaultRecords = $this->children($defaultRecord, 0);
                $childRecords = $this->localizedRecordsByDefaultRecords($defaultRecords, $language);
            }
        } else {
            $childRecords = $this->children($record, $language);
        }
        $childRecordByColPosKey = $this->recordsByColPosKey($childRecords);
        if ($defaultRecord === null) {
            $container = GeneralUtility::makeInstance(Container::class, $record, $childRecordByColPosKey, $language);
        } else {
            $container = GeneralUtility::makeInstance(Container::class, $defaultRecord, $childRecordByColPosKey, $language);
        }
        return $container;
    }

    protected function localizedRecordsByDefaultRecords(array $defaultRecords, int $language): array
    {
        $localizedRecords = $this->database->fetchOverlayRecords($defaultRecords, $language);
        $childRecords = $this->sortLocalizedRecordsByDefaultRecords($defaultRecords, $localizedRecords);
        return $childRecords;
    }

    protected function children(array $containerRecord, int $language): array
    {
        $records = $this->database->fetchRecordsByParentAndLanguage((int)$containerRecord['uid'], $language);
        $records = $this->workspaceOverlay($records);
        return $records;
    }

    protected function workspaceOverlay(array $records): array
    {
        $filtered = [];
        foreach ($records as $row) {
            BackendUtility::workspaceOL('tt_content', $row, $this->workspaceId, true);
            if ($row && !VersionState::cast($row['t3ver_state'] ?? 0)->equals(VersionState::DELETE_PLACEHOLDER)) {
                $filtered[] = $row;
            }
        }
        return $filtered;
    }

    protected function sortLocalizedRecordsByDefaultRecords(array $defaultRecords, array $localizedRecords): array
    {
        $sorted = [];
        foreach ($defaultRecords as $defaultRecord) {
            foreach ($localizedRecords as $localizedRecord) {
                if ($localizedRecord['l18n_parent'] === $defaultRecord['uid'] ||
                    $localizedRecord['l18n_parent'] === $defaultRecord['t3ver_oid']
                ) {
                    $sorted[] = $localizedRecord;
                }
            }
        }
        return $sorted;
    }

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
