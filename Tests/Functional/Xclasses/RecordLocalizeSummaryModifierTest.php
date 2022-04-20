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

use B13\Container\Xclasses\RecordLocalizeSummaryModifier;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RecordLocalizeSummaryModifierTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @test
     */
    public function getContainerUidsReturnsAllUids(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/two_container_elements.xml');
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo']
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [1, 2]);
        self::assertSame(2, count($containerUids));
    }

    /**
     * @test
     */
    public function getContainerChildrenReturnsHiddenRecords(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/hidden_child_record.xml');
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo']
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
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/hidden_container_record.xml');
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo']
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [1]);
        self::assertSame([1], $containerUids);
    }

    /**
     * @test
     */
    public function getContainerUidsReturnsAlsoUidsOfL18nParents(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/container_and_translated_container.xml');
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo']
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [2]);
        self::assertSame([2, 1], $containerUids);
    }
}
