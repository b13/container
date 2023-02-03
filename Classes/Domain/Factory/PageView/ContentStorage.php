<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory\PageView;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class ContentStorage
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

    abstract public function workspaceOverlay(array $records): array;

    abstract public function containerRecordWorkspaceOverlay(array $record): ?array;

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
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 11 && !empty($containerRecord['_ORIG_pid'])) {
            $pid = $containerRecord['_ORIG_pid'];
        }
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
}
