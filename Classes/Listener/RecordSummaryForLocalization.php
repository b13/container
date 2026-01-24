<?php

declare(strict_types=1);

namespace B13\Container\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Service\RecordLocalizeSummaryModifier;
use TYPO3\CMS\Backend\Controller\Event\AfterRecordSummaryForLocalizationEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;

class RecordSummaryForLocalization
{
    /**
     * @var RecordLocalizeSummaryModifier
     */
    protected $recordLocalizeSummaryModifier;

    protected ConnectionPool $connectionPool;

    public function __construct(
        RecordLocalizeSummaryModifier $recordLocalizeSummaryModifier,
        ConnectionPool $connectionPool
    ) {
        $this->recordLocalizeSummaryModifier = $recordLocalizeSummaryModifier;
        $this->connectionPool = $connectionPool;
    }

    public function __invoke(AfterRecordSummaryForLocalizationEvent $event): void
    {
        $records = $event->getRecords();
        if ((new Typo3Version())->getMajorVersion() < 14) {
            $columns = $event->getColumns();
            $records = $this->recordLocalizeSummaryModifier->filterRecords($records);
            $columns = $this->recordLocalizeSummaryModifier->rebuildColumns($columns);
            $event->setColumns($columns);
            $event->setRecords($records);
            return;
        }
        $localizeRecords = [];
        foreach ($records as $colPos => $recordsPerColPos) {
            foreach ($recordsPerColPos as $record) {
                $localizeRecords[$record['uid']] = $record;
            }
        }
        $fullRecords = $this->fetchAllRecords(array_keys($localizeRecords));
        $records = $this->moveContainerColPosIntoPageColPos($fullRecords, $localizeRecords);
        $event->setRecords($records);
    }

    protected function moveContainerColPosIntoPageColPos(array $records, array $localizeRecords): array
    {
        $recordsPerColPos = [];
        foreach ($records as $record) {
            $colPos = $this->resolveRecordColPos($record, $localizeRecords);
            if (!isset($recordsPerColPos[$colPos])) {
                $recordsPerColPos[$colPos] = [];
            }
            if (!isset($localizeRecords[$record['uid']])) {
                throw new RecordSummaryForLocalizationException('localizeRecord not set ' . $record['uid'], 1769247959);
            }
            $recordsPerColPos[$colPos][] = $localizeRecords[$record['uid']];
        }
        return $recordsPerColPos;
    }

    protected function resolveRecordColPos(array $record, $localizeRecords): int
    {
        if (($record['tx_container_parent'] ?? 0) === 0) {
            return $record['colPos'];
        }
        $loopCnt = 0;
        $maxDepth = 20;
        $containerUid = $record['tx_container_parent'];
        while (true) {
            if (in_array($containerUid, array_keys($localizeRecords))) {
                return $record['colPos'];
            }
            if ($loopCnt > $maxDepth) {
                throw new RecordSummaryForLocalizationException('maxDepth has reached ' . $maxDepth, 1769247958);
            }
            $containerRecord = $this->fetchOneRecord($containerUid);
            if ($containerRecord === null) {
                throw new RecordSummaryForLocalizationException('cannot fetch record for uid ' . $containerUid, 1769247957);
            }
            if (($containerRecord['tx_container_parent'] ?? 0) === 0) {
                return $containerRecord['colPos'];
            }
            $containerUid = $containerRecord['tx_container_parent'];
            $loopCnt++;
        }
    }

    protected function fetchOneRecord(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        return $row ?: null;
    }

    protected function fetchAllRecords(array $uids): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();
        $rowsPerUid = [];
        foreach ($rows as $row) {
            $rowsPerUid[$row['uid']] = $row;
        }
        return $rowsPerUid;
    }
}
