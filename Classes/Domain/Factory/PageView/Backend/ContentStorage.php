<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class ContentStorage
{
    /**
     * @var ?mixed[]
     */
    protected $records;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var int
     */
    protected $workspaceId = 0;

    public function __construct(Database $database, Context $context)
    {
        $this->database = $database;
        $this->workspaceId = (int)$context->getPropertyFromAspect('workspace', 'id');
    }

    protected function buildRecords(int $pid, int $language): array
    {
        $records = $this->database->fetchRecordsByPidAndLanguage($pid, $language);
        $records = $this->workspaceOverlay($records);
        $records = $this->recordsByContainer($records);
        return $records;
    }

    protected function recordsByContainer(array $records): array
    {
        $recordsByContainer = [];
        foreach ($records as $record) {
            if ($record['tx_container_parent'] > 0) {
                if (empty($recordsByContainer[$record['tx_container_parent']])) {
                    $recordsByContainer[$record['tx_container_parent']] = [];
                }
                $recordsByContainer[$record['tx_container_parent']][] = $record;
            }
        }
        return  $recordsByContainer;
    }

    public function getContainerChildren(array $containerRecord, int $language): array
    {
        $pid = (int)$containerRecord['pid'];
        if (isset($containerRecord['t3ver_oid']) && $containerRecord['t3ver_oid'] > 0) {
            $defaultContainerRecord = $this->database->fetchOneRecord((int)$containerRecord['t3ver_oid']);
            $uid = (int)$defaultContainerRecord['uid'];
        } else {
            $uid = (int)$containerRecord['uid'];
        }
        if (!isset($this->records[$pid][$language])) {
            $this->records[$pid][$language] = $this->buildRecords($pid, $language);
        }
        if (empty($this->records[$pid][$language][$uid])) {
            return [];
        }
        return $this->records[$pid][$language][$uid];
    }

    public function workspaceOverlay(array $records): array
    {
        $filtered = [];
        foreach ($records as $row) {
            BackendUtility::workspaceOL('tt_content', $row, $this->workspaceId, true);
            if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 12) {
                if ($row && VersionState::tryFrom($row['t3ver_state'] ?? 0) !== VersionState::DELETE_PLACEHOLDER) {
                    $filtered[] = $row;
                }
            } else {
                if ($row && !VersionState::cast($row['t3ver_state'] ?? 0)->equals(VersionState::DELETE_PLACEHOLDER)) {
                    $filtered[] = $row;
                }
            }
        }
        return $filtered;
    }

    public function containerRecordWorkspaceOverlay(array $record): ?array
    {
        BackendUtility::workspaceOL('tt_content', $record, $this->workspaceId, false);
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 12) {
            if ($record && VersionState::tryFrom($record['t3ver_state'] ?? 0) !== VersionState::DELETE_PLACEHOLDER) {
                return $record;
            }
        } else {
            if ($record && !VersionState::cast($record['t3ver_state'] ?? 0)->equals(VersionState::DELETE_PLACEHOLDER)) {
                return $record;
            }
        }
        return null;
    }
}
