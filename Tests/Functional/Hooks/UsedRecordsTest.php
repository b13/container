<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Hooks\UsedRecords;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UsedRecordsTest extends FunctionalTestCase
{

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    protected function getPageLayoutView(): PageLayoutView
    {
        if ((new Typo3Version())->getMajorVersion() < 11) {
            $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
                ->disableOriginalConstructor()
                ->getMock();
            return new PageLayoutView($eventDispatcher);
        }
        return new PageLayoutView();
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsTrueIfChildrenInContainer(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            self::markTestSkipped('TODO test v12');
        }
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Hooks/Fixtures/UsedRecords/children_in_container.csv');
        $pageLayout = $this->getPageLayoutView();
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class);
        $record = $this->fetchOneRecordByUid(2);
        $res = $usedRecords->addContainerChildren(['record' => $record, 'used' => false], $pageLayout);
        self::assertTrue($res);
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfChildrenHasWrongPid(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            self::markTestSkipped('TODO test v12');
        }
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Hooks/Fixtures/UsedRecords/children_in_container_wrong_pid.csv');
        $pageLayout = $this->getPageLayoutView();
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class);
        $record = $this->fetchOneRecordByUid(2);
        $res = $usedRecords->addContainerChildren(['record' => $record, 'used' => false], $pageLayout);
        self::assertFalse($res);
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfChildrenHasWrongColPos(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            self::markTestSkipped('TODO test v12');
        }
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Hooks/Fixtures/UsedRecords/children_in_container_wrong_colpos.csv');
        $pageLayout = $this->getPageLayoutView();
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class);
        $record = $this->fetchOneRecordByUid(2);
        $res = $usedRecords->addContainerChildren(['record' => $record, 'used' => false], $pageLayout);
        self::assertFalse($res);
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfRecordNotInContainer(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            self::markTestSkipped('TODO test v12');
        }
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Hooks/Fixtures/UsedRecords/children_not_in_container.csv');
        $pageLayout = $this->getPageLayoutView();
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class);
        $record = $this->fetchOneRecordByUid(2);
        $res = $usedRecords->addContainerChildren(['record' => $record, 'used' => false], $pageLayout);
        self::assertFalse($res);
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsTrueForLocalizedContent(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            self::markTestSkipped('TODO test v12');
        }
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Hooks/Fixtures/UsedRecords/localized_content.csv');
        $pageLayout = $this->getPageLayoutView();
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class);
        $record = $this->fetchOneRecordByUid(4);
        $res = $usedRecords->addContainerChildren(['record' => $record, 'used' => false], $pageLayout);
        self::assertTrue($res);
    }

    protected function fetchOneRecordByUid(int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAssociative();
        self::assertIsArray($row);
        return $row;
    }
}
