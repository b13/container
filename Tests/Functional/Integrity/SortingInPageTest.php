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

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Integrity\Database;
use B13\Container\Integrity\SortingInPage;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class SortingInPageTest extends FunctionalTestCase
{
    /**
     * @var SortingInPage
     */
    protected $sorting;

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
        $context = GeneralUtility::makeInstance(Context::class);
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $sortingDatabase = GeneralUtility::makeInstance(Database::class);
        $factoryDatabase = GeneralUtility::makeInstance(\B13\Container\Domain\Factory\Database::class, $context);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class, $factoryDatabase, $containerRegistry, $context);
        $containerService = GeneralUtility::makeInstance(ContainerService::class, $containerRegistry, $containerFactory);
        $this->sorting = GeneralUtility::makeInstance(SortingInPage::class, $sortingDatabase, $containerRegistry, $containerFactory, $containerService);
    }

    /**
     * @test
     */
    public function containerIsSortedAfterChildOfPreviousContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SortingInPage/container_is_sorted_before_child_of_previous_container.csv');
        $errors = $this->sorting->run(false);
        self::assertTrue(count($errors) === 1, 'should get one error');
        $rows = $this->getContentsByUid();
        self::assertTrue($rows[2]['sorting'] > $rows[3]['sorting'], 'container should be sorted after child of previous container');
    }

    /**
     * @test
     */
    public function containerIsSortedAfterChildOfPreviousContainerWithChangedChildrenSorting(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SortingInPage/container_is_sorted_before_child_of_previous_container_with_changed_children_sorting.csv');
        $errors = $this->sorting->run(false);
        self::assertTrue(count($errors) === 1, 'should get one error');
        $rows = $this->getContentsByUid();
        self::assertTrue($rows[2]['sorting'] > $rows[3]['sorting'], 'container should be sorted after child of previous container');
    }

    /**
     * @test
     */
    public function containerIsSortedAfterChildOfPreviousContainerWithNestedChangedChildrenSorting(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SortingInPage/container_is_sorted_before_child_of_previous_container_with_nested_changed_children_sorting.csv');
        $errors = $this->sorting->run(false);
        self::assertTrue(count($errors) === 1, 'should get one error');
        $rows = $this->getContentsByUid();
        self::assertTrue($rows[4]['sorting'] > $rows[3]['sorting'], 'container should be sorted after last nested child of previous container');
        self::assertTrue($rows[5]['sorting'] > $rows[4]['sorting'], 'child should be sorted after its own parent container after resorting');
    }

    /**
     * @test
     */
    public function nothingDoneForAlreadyCorrectSorted(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SortingInPage/correct_sorted.csv');
        $errors = $this->sorting->run();
        self::assertTrue(count($errors) === 0, 'should get no error');
    }

    protected function getContentsByUid(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $res = $queryBuilder->select('uid', 'sorting', 'colPos')
            ->from('tt_content')
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();
        $rows = [];
        foreach ($res as $row) {
            $rows[$row['uid']] = $row;
        }
        return $rows;
    }
}
