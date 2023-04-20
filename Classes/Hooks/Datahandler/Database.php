<?php

declare(strict_types=1);

namespace B13\Container\Hooks\Datahandler;

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
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Database implements SingletonInterface
{
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeByType(HiddenRestriction::class)
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class);
        return $queryBuilder;
    }

    public function fetchOneRecord(int $uid): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $record = $stm->fetch();
        } else {
            $record = $stm->fetchAssociative();
        }
        if ($record === false) {
            return null;
        }
        return $record;
    }

    public function fetchOverlayRecords(array $record): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter((int)$record['uid'], Connection::PARAM_INT)
                )
            );
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

    public function fetchOneTranslatedRecordByl10nSource(int $uid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'l10n_source',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $record = $stm->fetch();
        } else {
            $record = $stm->fetchAssociative();
        }
        if ($record === false) {
            return null;
        }
        return $record;
    }

    public function fetchOneTranslatedRecordByLocalizationParent(int $uid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
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
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $record = $stm->fetch();
        } else {
            $record = $stm->fetchAssociative();
        }
        if ($record === false) {
            return null;
        }
        return $record;
    }

    public function fetchRecordsByParentAndLanguage(int $parent, int $language): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
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
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting', 'ASC');
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

    public function fetchContainerRecordLocalizedFreeMode(int $defaultUid, int $language): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'l10n_source',
                    $queryBuilder->createNamedParameter($defaultUid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'l18n_parent',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            );
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            $stm = $stm->executeQuery();
        } else {
            $stm = $stm->execute();
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 10) {
            $record = $stm->fetch();
        } else {
            $record = $stm->fetchAssociative();
        }
        if ($record === false) {
            return null;
        }
        return $record;
    }
}
