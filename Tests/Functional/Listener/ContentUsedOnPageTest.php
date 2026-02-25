<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Listener\ContentUsedOnPage;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ContentUsedOnPageTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    #[Test]
    public function addContainerChildrenReturnsTrueIfChildrenInContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Hooks/Fixtures/UsedRecords/children_in_container.csv');
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        $record = $this->fetchOneRecordByUid(2);
        $event = new IsContentUsedOnPageLayoutEvent($record, true, $pageLayoutContext);
        $listener = GeneralUtility::makeInstance(ContentUsedOnPage::class);
        $listener($event);
        self::assertTrue($event->isRecordUsed());
    }

    #[Test]
    public function addContainerChildrenReturnsFalseIfChildrenHasWrongPid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Hooks/Fixtures/UsedRecords/children_in_container_wrong_pid.csv');
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        $record = $this->fetchOneRecordByUid(2);
        $event = new IsContentUsedOnPageLayoutEvent($record, false, $pageLayoutContext);
        $listener = GeneralUtility::makeInstance(ContentUsedOnPage::class);
        $listener($event);
        self::assertFalse($event->isRecordUsed());
    }

    #[Test]
    public function addContainerChildrenReturnsFalseIfChildrenHasWrongColPos(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Hooks/Fixtures/UsedRecords/children_in_container_wrong_colpos.csv');
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        $record = $this->fetchOneRecordByUid(2);
        $event = new IsContentUsedOnPageLayoutEvent($record, false, $pageLayoutContext);
        $listener = GeneralUtility::makeInstance(ContentUsedOnPage::class);
        $listener($event);
        self::assertFalse($event->isRecordUsed());
    }

    #[Test]
    public function addContainerChildrenReturnsFalseIfRecordNotInContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Hooks/Fixtures/UsedRecords/children_not_in_container.csv');
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        $record = $this->fetchOneRecordByUid(2);
        $event = new IsContentUsedOnPageLayoutEvent($record, false, $pageLayoutContext);
        $listener = GeneralUtility::makeInstance(ContentUsedOnPage::class);
        $listener($event);
        self::assertFalse($event->isRecordUsed());
    }

    #[Test]
    public function addContainerChildrenReturnsTrueForLocalizedContent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Hooks/Fixtures/UsedRecords/localized_content.csv');
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        $record = $this->fetchOneRecordByUid(4);
        $event = new IsContentUsedOnPageLayoutEvent($record, false, $pageLayoutContext);
        $listener = GeneralUtility::makeInstance(ContentUsedOnPage::class);
        $listener($event);
        self::assertTrue($event->isRecordUsed());
    }

    protected function fetchOneRecordByUid(int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($row);
        return $row;
    }
}
