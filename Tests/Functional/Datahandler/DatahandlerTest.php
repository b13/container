<?php

namespace B13\Container\Tests\Functional\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class DatahandlerTest extends FunctionalTestCase
{
    protected $typo3MajorVersion;

    /**
     * @var DataHandler
     */
    protected $dataHandler;

    /**
     * @var BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['workspaces'];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $this->typo3MajorVersion = $typo3Version->getMajorVersion();
    }

    protected function linkSiteConfigurationIntoTestInstance(): void
    {
        $from = ORIGINAL_ROOT . '/typo3conf/sites';
        $to = $this->getInstancePath() . '/typo3conf/sites';
        if (!is_dir($from)) {
            throw new \Exception('site config directory not found', 1630425034);
        }
        if (!file_exists($to)) {
            $success = symlink(realpath($from), $to);
            if ($success === false) {
                throw new \Exception('cannot link site config', 1630425035);
            }
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->backendUser = $this->setUpBackendUserFromFixture(1);
        $GLOBALS['BE_USER'] = $this->backendUser;
        Bootstrap::initializeLanguageObject();
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        return $queryBuilder;
    }

    /**
     * @param string $field
     * @param int $id
     * @return array
     */
    protected function fetchOneRecord(string $field, int $id): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    $field,
                    $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAssociative();
        self::assertIsArray($row, 'cannot fetch row for field ' . $field . ' with id ' . $id);
        return $row;
    }
}
