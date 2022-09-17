<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use B13\Container\Xclasses\RecordLocalizeSummaryModifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RecordLocalizeSummaryModifierTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @test
     */
    public function getContainerUidsReturnsAllUids(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/two_container_elements.csv');
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo'],
            ['containerRegistry' => $containerRegistry]
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [1, 2]);
        self::assertSame(2, count($containerUids));
    }

    /**
     * @test
     */
    public function getContainerChildrenReturnsHiddenRecords(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/hidden_child_record.csv');
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo'],
            ['containerRegistry' => $containerRegistry]
        );
        $containerChildren = $recordLocalizeSummeryModifier->_call('getContainerChildren', [1]);
        self::assertTrue(isset($containerChildren[1]));
        self::assertIsArray($containerChildren[1]);
    }

    /**
     * @test
     */
    public function getContainerUidsReturnsHiddenUids(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/hidden_container_record.csv');
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo'],
            ['containerRegistry' => $containerRegistry]
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [1]);
        self::assertSame([1], $containerUids);
    }

    /**
     * @test
     */
    public function getContainerUidsReturnsAlsoUidsOfL18nParents(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/container_and_translated_container.csv');
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo'],
            ['containerRegistry' => $containerRegistry]
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [2]);
        self::assertSame([2, 1], $containerUids);
    }
}
