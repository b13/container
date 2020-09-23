<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendWorkspaceRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class Database implements SingletonInterface
{
    /**
     * @var int
     */
    protected $backendUserId = 0;

    /**
     * @var int
     */
    protected $workspaceId = 0;

    public function __construct(Context $context = null)
    {
        if ($context === null) {
            $context = GeneralUtility::makeInstance(Context::class);
        }
        $this->backendUserId = (int)$context->getPropertyFromAspect('backend.user', 'id', 0);
        $this->workspaceId = (int)$context->getPropertyFromAspect('workspace', 'id');
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        if (TYPO3_MODE === 'BE') {
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } elseif (TYPO3_MODE === 'FE') {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            if ($this->backendUserId > 0) {
                $queryBuilder->getRestrictions()->removeByType(FrontendWorkspaceRestriction::class);
            }
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
     * @param int $language
     * @return array
     */
    public function fetchRecordsByParentAndLanguage(int $parent, int $language): array
    {
        $queryBuilder = $this->getQueryBuilder();

        return  (array)$queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter($parent, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->notIn(
                    't3ver_state',
                    $queryBuilder->createNamedParameter(
                        [VersionState::NEW_PLACEHOLDER, VersionState::MOVE_PLACEHOLDER],
                        Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->in(
                    't3ver_wsid',
                    $queryBuilder->createNamedParameter([0, $this->workspaceId], Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();
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
            if ($record['t3ver_oid'] > 0) {
                $uids[] = $record['t3ver_oid'];
            }
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
                ),
                $queryBuilder->expr()->notIn(
                    't3ver_state',
                    $queryBuilder->createNamedParameter(
                        [VersionState::NEW_PLACEHOLDER, VersionState::MOVE_PLACEHOLDER],
                        Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->in(
                    't3ver_wsid',
                    $queryBuilder->createNamedParameter([0, $this->workspaceId], Connection::PARAM_INT_ARRAY)
                )
            )
            ->execute()
            ->fetchAll();
        return $records;
    }

    public function fetchUidsHavingWorkspaceVersion(array $records, int $workspaceId): array
    {
        if (empty($records)) {
            return [];
        }
        $uids = [];
        foreach ($records as $record) {
            $uids[] = $record['uid'];
        }
        $queryBuilder = $this->getQueryBuilder();
        return  (array)$queryBuilder->select('t3ver_oid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->eq(
                    't3ver_wsid',
                    $queryBuilder->createNamedParameter($workspaceId, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
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
