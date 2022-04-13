<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Integrity\Error\WrongPidError;
use B13\Container\Integrity\Integrity;
use B13\Container\Integrity\IntegrityFix;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\ContentFetcher;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class IntegrityTest extends FunctionalTestCase
{

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    /**
     * @test
     */
    public function integrityCreateWrongPidError(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Integrity/Fixtures/children_with_wrong_pids.xml');
        $integrity = GeneralUtility::makeInstance(Integrity::class);
        $res = $integrity->run();
        self::assertTrue(isset($res['errors']));
        self::assertSame(1, count($res['errors']));
        /** @var WrongPidError $error */
        $error = $res['errors'][0];
        self::assertTrue($error instanceof WrongPidError);
        $record = $error->getChildRecord();
        self::assertSame(2, $record['uid']);
        $container = $error->getContainerRecord();
        self::assertSame(1, $container['uid']);
    }

    /**
     * @test
     */
    public function wrongPidErrorElementsAreShownAsUnusedElements(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Integrity/Fixtures/children_with_wrong_pids.xml');

        $GLOBALS['BE_USER'] = $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();

        $backendLayout = new BackendLayout(
            'foo',
            'bar',
            ['__colPosList' => [0]]
        );
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $pageRecord = $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $pageLayoutContext = new PageLayoutContext($pageRecord, $backendLayout);
        $contentFetcher = new ContentFetcher($pageLayoutContext);
        $unusedRecords = $contentFetcher->getUnusedRecords();
        $unusedRecordsArr = [];
        foreach ($unusedRecords as $unusedRecord) {
            $unusedRecordsArr[] = $unusedRecord;
        }
        self::assertSame(1, count($unusedRecordsArr));
        self::assertSame(2, $unusedRecordsArr[0]['uid']);
    }

    /**
     * @test
     */
    public function integrityFixDeleteChildrenWithWrongPid(): void
    {
        $GLOBALS['BE_USER'] = $this->setUpBackendUserFromFixture(1);
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Integrity/Fixtures/children_with_wrong_pids.xml');
        $integrity = GeneralUtility::makeInstance(Integrity::class);
        $res = $integrity->run();
        $integrityFix = GeneralUtility::makeInstance(IntegrityFix::class);
        foreach ($res['errors'] as $error) {
            $integrityFix->deleteChildrenWithWrongPid($error);
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $record = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        self::assertSame(1, $record['deleted']);
    }
}
