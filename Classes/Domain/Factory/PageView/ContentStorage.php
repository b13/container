<?php

namespace B13\Container\Domain\Factory\PageView;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
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


    public function __construct(Database $database = null)
    {
        if ($database === null) {
            $database = GeneralUtility::makeInstance(Database::class);
        }
        $this->database = $database;
    }

    protected function buildRecords($pid, $language)
    {
        $records = $this->database->fetchRecordsByPidAndLanguage($pid, $language);
        $records = $this->recordsByContainer($records);
        return $records;
    }

    protected function recordsByContainer(array $records)
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

    public function getContainerChildren(array $containerRecord, $language)
    {
        $pid = $containerRecord['pid'];
        if (!empty($containerRecord['_ORIG_pid'])) {
            $pid = $containerRecord['_ORIG_pid'];
        }
        $uid = $containerRecord['uid'];
        if (!isset($this->records[$pid][$language])) {
            $this->records[$pid][$language] = $this->buildRecords($pid, $language);
        }
        if (empty($this->records[$pid][$language][$uid])) {
            return [];
        }
        return $this->records[$pid][$language][$uid];
    }
}
