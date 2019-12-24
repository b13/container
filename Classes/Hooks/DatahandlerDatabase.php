<?php

namespace  B13\Container\Hooks;


use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatahandlerDatabase implements SingletonInterface
{

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        return $queryBuilder;
    }

    /**
     * @param int $uid
     * @return array|null
     */
    public function fetchOneRecord(int $uid): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $record = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        if ($record === false) {
            return null;
        }
        return $record;
    }

    /**
     * @param array $record
     * @return array
     */
    public function fetchOverlayRecords(array $record): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $records = (array)$queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter($record['uid'], Connection::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();
        return $records;
    }
}
