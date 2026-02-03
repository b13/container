<?php

declare(strict_types=1);

namespace B13\Container\Tests\Unit\Service;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Service\RecordLocalizeSummaryModifier;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class RecordLocalizeSummaryModifierTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function filterRecordsRemovesContainerChildrenIfParentContainerIsTranslatedAsWell(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 14) {
            self::markTestSkipped('not used in v14');
        }
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
        $filtered = $recordLocalizeSummeryModifier->filterRecords($recordsToTranslate);
        self::assertTrue(count($filtered[0]) === 1);
        self::assertTrue(empty($filtered[200]));
    }

    /**
     * @test
     */
    public function filterRecordsKeepsContainerChildrenIfParentContainerIsNotTranslated(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 14) {
            self::markTestSkipped('not used in v14');
        }
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
        $filtered = $recordLocalizeSummeryModifier->filterRecords($recordsToTranslate);
        self::assertTrue(count($filtered[0]) === 1);
        self::assertTrue(count($filtered[200]) === 1);
    }

    /**
     * @test
     */
    public function rebuildColumnsReturnsColumnListWithConsecutiveArrayKeysAlsoWhenRegistryReturnsRepeatingColumns(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 14) {
            self::markTestSkipped('not used in v14');
        }
        $tcaRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllAvailableColumns'])
            ->getMock();
        $tcaRegistry->expects(self::once())->method('getAllAvailableColumns')->willReturn(
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
            [$tcaRegistry]
        );
        $rebuildedColumns = $recordLocalizeSummeryModifier->rebuildColumns($columns);
        self::assertSame([2, 3, 0], $rebuildedColumns['columnList']);
    }
}
