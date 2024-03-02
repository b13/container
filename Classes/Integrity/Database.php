<?php

declare(strict_types=1);

namespace B13\Container\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Database implements SingletonInterface
{
    private $fields = ['uid', 'pid', 'sys_language_uid', 'CType', 'l18n_parent', 't3_origuid', 'colPos', 'tx_container_parent', 'l10n_source', 'hidden', 'sorting'];

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder;
    }

    public function getNonDefaultLanguageContainerRecords(array $cTypes): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($cTypes, Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilder->expr()->gt(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $results = $stm->fetchAll();
        } else {
            $results = $stm->fetchAllAssociative();
        }
        $rows = [];
        foreach ($results as $result) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getNonDefaultLanguageContainerChildRecords(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->gt(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->gt(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $results = $stm->fetchAll();
        } else {
            $results = $stm->fetchAllAssociative();
        }
        $rows = [];
        foreach ($results as $result) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getChildrenByContainerAndColPos(int $containerId, int $colPos, int $languageId): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter($containerId, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'colPos',
                    $queryBuilder->createNamedParameter($colPos, Connection::PARAM_INT)
                )
            )
            ->orderBy('sorting');
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            return (array)$stm->fetchAll();
        }
        return (array)$stm->fetchAllAssociative();
    }

    public function getNonContainerChildrenPerColPos(array $containerUsedColPosArray, ?int $pid = null): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->notIn(
                    'colPos',
                    $queryBuilder->createNamedParameter($containerUsedColPosArray, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        if (!empty($pid)) {
            $stm->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)
                )
            );
        }
        $stm->orderBy('pid');
        $stm->addOrderBy('colPos');
        $stm->addOrderBy('sorting');
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $results = $stm->fetchAll();
        } else {
            $results = $stm->fetchAllAssociative();
        }
        $rows = [];
        foreach ($results as $result) {
            $key = $result['pid'] . '-' . $result['colPos'];
            if (!isset($rows[$key])) {
                $rows[$key] = [];
            }
            $rows[$key][$result['uid']] = $result;
        }
        return $rows;
    }

    public function getContainerRecords(array $cTypes, ?int $pid = null): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($cTypes, Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        if (!empty($pid)) {
            $stm->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)
                )
            );
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $results = $stm->fetchAll();
        } else {
            $results = $stm->fetchAllAssociative();
        }
        $rows = [];
        foreach ($results as $result) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getContainerRecordsFreeMode(array $cTypes, ?int $pid = null): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($cTypes, Connection::PARAM_STR_ARRAY)
                ),
                $queryBuilder->expr()->neq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        if (!empty($pid)) {
            $stm->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)
                )
            );
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $results = $stm->fetchAll();
        } else {
            $results = $stm->fetchAllAssociative();
        }
        $rows = [];
        foreach ($results as $result) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getContainerChildRecords(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select(...$this->fields)
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->gt(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $results = $stm->fetchAll();
        } else {
            $results = $stm->fetchAllAssociative();
        }
        $rows = [];
        foreach ($results as $result) {
            $rows[$result['uid']] = $result;
        }
        return $rows;
    }

    public function getSortingByUid(int $uid): ?int
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder
            ->select('sorting')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->execute();
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $row = $stm->fetch();
        } else {
            $row = $stm->fetchAssociative();
        }
        if ($row === false || !isset($row['sorting'])) {
            return null;
        }
        return $row['sorting'];
    }
}
