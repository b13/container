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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendWorkspaceRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public function __construct(Context $context)
    {
        $this->backendUserId = (int)$context->getPropertyFromAspect('backend.user', 'id', 0);
        $this->workspaceId = (int)$context->getPropertyFromAspect('workspace', 'id');
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        if ($this->getServerRequest() instanceof ServerRequestInterface
            && ApplicationType::fromRequest($this->getServerRequest())->isFrontend()
        ) {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            // do not use FrontendWorkspaceRestriction
            $queryBuilder->getRestrictions()
                ->removeByType(FrontendWorkspaceRestriction::class)
                ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $this->workspaceId));
        } else {
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $this->workspaceId));
        }
        return $queryBuilder;
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    public function fetchRecordsByPidAndLanguage(int $pid, int $language): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
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

    public function fetchOneRecord(int $uid): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
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

    public function fetchOneDefaultRecord(array $record): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($record['l18n_parent'], \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
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
        $stm = $queryBuilder->select('*')
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

    public function fetchOneOverlayRecord(int $uid, int $language): ?array
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
}
