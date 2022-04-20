<?php

declare(strict_types=1);
namespace B13\Container\Tests\Unit\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use B13\Container\Xclasses\RecordLocalizeSummaryModifier;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class RecordLocalizeSummaryModifierTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function filterRecordsRemovesContainerChildrenIfParentContainerIsTranslatedAsWell(): void
    {
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['getContainerUids', 'getContainerChildren'],
            [],
            '',
            false
        );
        $recordLocalizeSummeryModifier->expects(self::once())->method('getContainerUids')->willReturn([1]);
        $recordLocalizeSummeryModifier->expects(self::once())->method('getContainerChildren')->willReturn([2 => ['uid' => 2, 'tx_container_parent' => 1]]);
        $recordsToTranslate = [
            0 => [['uid' => 1]],
            200 => [['uid' => 2]],
        ];
        $filtered = $recordLocalizeSummeryModifier->_call('filterRecords', $recordsToTranslate);
        self::assertTrue(1 === count($filtered[0]));
        self::assertTrue(empty($filtered[200]));
    }

    /**
     * @test
     */
    public function filterRecordsKeepsContainerChildrenIfParentContainerIsNotTranslated(): void
    {
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['getContainerUids', 'getContainerChildren'],
            [],
            '',
            false
        );
        $recordLocalizeSummeryModifier->expects(self::once())->method('getContainerUids')->willReturn([3]);
        $recordLocalizeSummeryModifier->expects(self::once())->method('getContainerChildren')->willReturn([2 => ['uid' => 2, 'tx_container_parent' => 1]]);
        $recordsToTranslate = [
            0 => [['uid' => 3]],
            200 => [['uid' => 2]],
        ];
        $filtered = $recordLocalizeSummeryModifier->_call('filterRecords', $recordsToTranslate);
        self::assertTrue(1 === count($filtered[0]));
        self::assertTrue(1 === count($filtered[200]));
    }

    /**
     * @test
     */
    public function rebuildColumnsReturnsColumnListWithConsecutiveArrayKeysAlsoWhenRegistryReturnsRepeatingColumns(): void
    {
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAllAvailableColumns()->willReturn(
            [
                ['colPos' => 2],
                ['colPos' => 3],
                ['colPos' => 2],
            ]
        );
        $columns = ['columns' => [0 => 'main'], 'columnList' => [0]];
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['getContainerUids', 'getContainerChildren'],
            [$tcaRegistry->reveal()]
        );
        $rebuildedColumns = $recordLocalizeSummeryModifier->_call('rebuildColumns', $columns);
        self::assertSame([2, 3, 0], $rebuildedColumns['columnList']);
    }
}
