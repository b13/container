<?php

namespace B13\Container\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordLocalizeSummaryModifier implements SingletonInterface
{
    /**
     * @var Registry
     */
    protected $containerRegistry;

    public function __construct(Registry $containerRegistry = null)
    {
        if ($containerRegistry === null) {
            $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        }
        $this->containerRegistry = $containerRegistry;
    }

    public function rebuildPayload(array $payload)
    {
        return [
            'records' => $this->filterRecords($payload['records']),
            'columns' => $this->rebuildColumns($payload['columns'])
        ];
    }

    protected function filterRecords(array $recordsPerColPos)
    {
        // cannot be done by event in v10
        $uids = [];
        foreach ($recordsPerColPos as $colPos => $records) {
            foreach ($records as $record) {
                $uids[] = $record['uid'];
            }
        }
        if (empty($uids)) {
            return $recordsPerColPos;
        }
        $containerUids = $this->getContainerUids($uids);
        if (empty($containerUids)) {
            return $recordsPerColPos;
        }
        $containerChildren = $this->getContainerChildren($uids);
        if (empty($containerChildren)) {
            return $recordsPerColPos;
        }
        // we have both: container to translate and container children to translate
        // unset all records in container to translate
        $filtered = [];
        foreach ($recordsPerColPos as $colPos => $records) {
            $filteredRecords = [];
            foreach ($records as $record) {
                if (empty($containerChildren[$record['uid']])) {
                    $filteredRecords[] = $record;
                } else {
                    $fullRecord = $containerChildren[$record['uid']];
                    if (!in_array($fullRecord['tx_container_parent'], $containerUids, true)) {
                        $filteredRecords[] = $record;
                    }
                }
            }
            if (!empty($filteredRecords)) {
                $filtered[$colPos] = $filteredRecords;
            }
        }
        return $filtered;
    }

    protected function rebuildColumns(array $columns)
    {
        // this can be done with AfterPageColumnsSelectedForLocalizationEvent event in v10
        $containerColumns = $this->containerRegistry->getAllAvailableColumns();
        foreach ($containerColumns as $containerColumn) {
            $columns = [
                'columns' => array_replace([$containerColumn['colPos'] => 'Container Children (' . $containerColumn['colPos'] . ')'], $columns['columns']),
                'columnList' => array_values(array_unique(array_merge([$containerColumn['colPos']], $columns['columnList'])))
            ];
        }
        return $columns;
    }

    // database helper

    protected function getContainerUids(array $uids)
    {
        $containerCTypes = $this->containerRegistry->getRegisteredCTypes();
        if (empty($containerCTypes)) {
            return [];
        }
        $cTypes = [];
        foreach ($containerCTypes as $containerCType) {
            $cTypes[] = '"' . $containerCType . '"';
        }

        $rows =  (array)$this->getDatabase()
            ->exec_SELECTgetRows(
                'uid',
                'tt_content',
                'uid IN (' . implode(',', $uids) . ') AND CType IN (' . $cTypes . ') AND deleted=0'
            );
        $containerUids = [];
        foreach ($rows as $row) {
            $containerUids[] = (int)$row['uid'];
        }
    }

    protected function getContainerChildren(array $uids)
    {
        $containerChildren = [];

        $rows =  (array)$this->getDatabase()
            ->exec_SELECTgetRows(
                '*',
                'tt_content',
                'uid IN (' . implode(',', $uids) . ') AND tx_container_parent>0 AND deleted=0'
            );

        foreach ($rows as $row) {
            $containerChildren[$row['uid']] = $row;
        }
        return $containerChildren;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
