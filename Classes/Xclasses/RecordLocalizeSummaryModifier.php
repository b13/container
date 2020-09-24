<?php

declare(strict_types=1);

namespace B13\Container\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
        $this->containerRegistry = $containerRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    public function rebuildPayload(array $payload): array
    {
        return [
            'records' => $this->filterRecords($payload['records']),
            'columns' => $this->rebuildColumns($payload['columns'])
        ];
    }

    protected function filterRecords(array $recordsPerColPos): array
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

    protected function rebuildColumns(array $columns): array
    {
        // this can be done with AfterPageColumnsSelectedForLocalizationEvent event in v10
        $containerColumns= $this->containerRegistry->getAllAvailableColumns();
        foreach ($containerColumns as $containerColumn) {
            $columns = [
                'columns' => array_replace([$containerColumn['colPos'] => 'Container Children (' . $containerColumn['colPos'] . ')'], $columns['columns']),
                'columnList' => array_unique(array_merge([$containerColumn['colPos']], $columns['columnList']))
            ];
        }
        return $columns;
    }

    // database helper

    protected function getContainerUids(array $uids): array
    {
        $containerCTypes = $this->containerRegistry->getRegisteredCTypes();
        if (empty($containerCTypes)) {
            return [];
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        return (array)$queryBuilder->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($containerCTypes, Connection::PARAM_STR_ARRAY)
                )
            )
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }

    protected function getContainerChildren(array $uids): array
    {
        $containerChildren = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->neq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute();
        while ($row = $stm->fetch()) {
            $containerChildren[$row['uid']] = $row;
        }
        return $containerChildren;
    }
}
