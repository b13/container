<?php

namespace B13\Container;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class Database implements SingletonInterface
{

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
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
     * @param int $parent
     * @param int $colPos
     * @return array
     */
    public function fetchRecordsByParentAndColPos(int $parent, int $colPos): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $records = (array)$queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter($parent, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'colPos',
                    $queryBuilder->createNamedParameter($colPos, \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();
        return $records;
    }

    /**
     * @param int $parent
     * @param int $colPos
     * @return array
     */
    public function fetchRecordsByParentAndColPosIncludeHidden(int $parent, int $colPos): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $records = (array)$queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter($parent, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'colPos',
                    $queryBuilder->createNamedParameter($colPos, \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();
        return $records;
    }
}
