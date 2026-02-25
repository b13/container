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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Context\PageContext;
use TYPO3\CMS\Backend\Domain\Model\Language\PageLanguageInformation;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\ContentFetcher;
use TYPO3\CMS\Backend\View\Drawing\DrawingConfiguration;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class IntegrityTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    #[Test]
    public function integrityCreateWrongPidError(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/children_with_wrong_pids.csv');
        $integrity = $this->get(Integrity::class);
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

    #[Test]
    public function wrongPidErrorElementsAreShownAsUnusedElements(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/children_with_wrong_pids.csv');

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);

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
                    $queryBuilder->createNamedParameter(2, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        $site = $this->getMockBuilder(Site::class)->disableOriginalConstructor()->getMock();
        $drawingConfiguration = $this->getMockBuilder(DrawingConfiguration::class)->disableOriginalConstructor()->getMock();
        $serverRequest = $this->getMockBuilder(ServerRequest::class)->disableOriginalConstructor()->getMock();
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 14) {
            $pageLayoutContext = new PageLayoutContext($pageRecord, $backendLayout, $site, $drawingConfiguration, $serverRequest);
            $contentFetcher = new ContentFetcher($pageLayoutContext);
            $unusedRecords = $contentFetcher->getUnusedRecords();
        } else {
            $pageLanguageInformation = new PageLanguageInformation(
                $pageRecord['uid'],
                [],
                [],
                [],
                [0],
                false,
                []
            );
            $pageContext = new PageContext(
                $pageRecord['uid'],
                $pageRecord,
                $site,
                [],
                [],
                [],
                $pageLanguageInformation,
                new Permission()
            );
            $pageLayoutContext = new PageLayoutContext($pageContext, $backendLayout, $drawingConfiguration, $serverRequest);
            $contentFetcher = $this->get(ContentFetcher::class);
            $unusedRecords = $contentFetcher->getUnusedRecords($pageLayoutContext);
        }

        $unusedRecordsArr = [];
        foreach ($unusedRecords as $unusedRecord) {
            $unusedRecordsArr[] = $unusedRecord;
        }
        self::assertSame(1, count($unusedRecordsArr));
        self::assertSame(2, $unusedRecordsArr[0]['uid']);
    }

    #[Test]
    public function integrityFixDeleteChildrenWithWrongPid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        $this->importCSVDataSet(__DIR__ . '/Fixtures/children_with_wrong_pids.csv');
        $integrity = $this->get(Integrity::class);
        $res = $integrity->run();
        $integrityFix = $this->get(IntegrityFix::class);
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
                    $queryBuilder->createNamedParameter(2, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        self::assertSame(1, $record['deleted']);
    }
}
