<?php

declare(strict_types=1);

namespace B13\Container\Service;

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
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordLocalizeSummaryModifier implements SingletonInterface
{
    /**
     * @var Registry
     */
    protected $containerRegistry;

    public function __construct(Registry $containerRegistry)
    {
        $this->containerRegistry = $containerRegistry;
    }

    public function rebuildPayload(array $payload): array
    {
        return [
            'records' => $this->filterRecords($payload['records']),
            'columns' => $this->rebuildColumns($payload['columns']),
        ];
    }

    public function filterRecords(array $recordsPerColPos): array
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

    public function rebuildColumns(array $columns): array
    {
        // this can be done with AfterPageColumnsSelectedForLocalizationEvent event in v10
        $containerColumns = $this->containerRegistry->getAllAvailableColumns();
        foreach ($containerColumns as $containerColumn) {
            $columns = [
                'columns' => array_replace([$containerColumn['colPos'] => 'Container Children (' . $containerColumn['colPos'] . ')'], $columns['columns']),
                'columnList' => array_values(array_unique(array_merge([$containerColumn['colPos']], $columns['columnList']))),
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
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('uid', 'l18n_parent')
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
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $rows = $stm->fetchAll();
        } else {
            $rows = $stm->fetchAllAssociative();
        }
        $containerUids = [];
        foreach ($rows as $row) {
            $containerUids[] = $row['uid'];
            if ($row['l18n_parent'] > 0) {
                $containerUids[] = $row['l18n_parent'];
            }
        }
        return $containerUids;
    }

    protected function getContainerChildren(array $uids): array
    {
        $containerChildren = [];
        $queryBuilder = $this->getQueryBuilder();
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
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $rows = $stm->fetchAll();
        } else {
            $rows = $stm->fetchAllAssociative();
        }
        foreach ($rows as $row) {
            $containerChildren[$row['uid']] = $row;
        }
        return $containerChildren;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder;
    }
}
