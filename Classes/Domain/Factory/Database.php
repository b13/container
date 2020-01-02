<?php

namespace B13\Container\Domain\Factory;

use TYPO3\CMS\Core\Database\Connection;
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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        if (TYPO3_MODE === 'BE') {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }
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
     * @return array|null
     */
    public function fetchOneDefaultRecord(array $record): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $record = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($record['l18n_parent'], \PDO::PARAM_INT)
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
    public function fetchRecordsByParentAndLanguage(int $parent, int $language): array
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
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();
        return $records;
    }


    /**
     * @param array $records
     * @param int $language
     * @return array
     */
    public function fetchOverlayRecords(array $records, int $language): array
    {
        $uids = [];
        foreach ($records as $record) {
            $uids[] = $record['uid'];
        }
        $queryBuilder = $this->getQueryBuilder();
        $records = (array)$queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();

        return $records;
    }

    /**
     * @param int $uid
     * @param int $language
     * @return array
     */
    public function fetchOneOverlayRecord(int $uid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $record = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        if ($record === false) {
            return null;
        }
        return $record;
    }
}
