<?php

namespace B13\Container\Tests\Unit\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Hooks\Datahandler\DatamapBeforeStartHook;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class DatamapBeforeStartHookTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function datamapForLocalizationsExtendsDatamapWithLocalizations()
    {
        $database = $this->prophesize(Database::class);
        $defaultRecord = [
            'uid' => 2,
            'tx_container_parent' => 0,
            'sys_language_uid' => 0
        ];
        $database->fetchOverlayRecords($defaultRecord)->willReturn([['uid' => 3]]);
        $database->fetchOneRecord(2)->willReturn($defaultRecord);

        $dataHandlerHook = $this->getAccessibleMock(
            DatamapBeforeStartHook::class,
            ['foo'],
            ['containerFactory' => null, 'database' => $database->reveal()]
        );
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => 200,
                    'tx_container_parent' => 1,
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $modDatamap = $dataHandlerHook->_call('datamapForChildLocalizations', $datamap);
        self::assertTrue(is_array($modDatamap['tt_content'][3]));
        self::assertSame(1, $modDatamap['tt_content'][3]['tx_container_parent']);
    }
    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToSplittedColPosValue()
    {
        $dataHandlerHook = $this->getAccessibleMock(DatamapBeforeStartHook::class, ['foo']);
        $datamap = [
            'tt_content' => [
                39 => [
                    'colPos' => '2-34',
                    'sys_language_uid' => 0
                ]
            ]
        ];
        $datamap = $dataHandlerHook->_call('extractContainerIdFromColPosInDatamap', $datamap);
        self::assertSame(34, $datamap['tt_content'][39]['colPos']);
        self::assertSame(2, $datamap['tt_content'][39]['tx_container_parent']);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToZeroValue()
    {
        $dataHandlerHook = $this->getAccessibleMock(DatamapBeforeStartHook::class, ['foo']);
        $datamap = [
            'tt_content' => [
                39 => [
                    'colPos' => '0',
                    'sys_language_uid' => 0
                ]
            ]
        ];
        $datamap = $dataHandlerHook->_call('extractContainerIdFromColPosInDatamap', $datamap);
        self::assertSame(0, $datamap['tt_content'][39]['colPos']);
        self::assertSame(0, $datamap['tt_content'][39]['tx_container_parent']);
    }
}
