<?php

declare(strict_types=1);

namespace B13\Container\Tests\Unit\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Hooks\Datahandler\DatamapBeforeStartHook;
use B13\Container\Tca\Registry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DatamapBeforeStartHookTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function datamapForLocalizationsExtendsDatamapWithLocalizations(): void
    {
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $defaultRecord = [
            'uid' => 2,
            'tx_container_parent' => 0,
            'sys_language_uid' => 0,
        ];
        $database = $this->getMockBuilder(Database::class)
            ->onlyMethods(['fetchOverlayRecords', 'fetchOneRecord'])
            ->getMock();
        $database->expects(self::once())->method('fetchOverlayRecords')->with($defaultRecord)->willReturn([['uid' => 3]]);
        $database->expects(self::once())->method('fetchOneRecord')->with(2)->willReturn($defaultRecord);
        $containerRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)->disableOriginalConstructor()->getMock();
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(DatamapBeforeStartHook::class))
            ->setConstructorArgs([
                'containerFactory' => $containerFactory,
                'database' => $database,
                'tcaRegistry' => $containerRegistry,
                'containerService' => $containerService,
            ])
            ->onlyMethods([])
            ->getMock();
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => 200,
                    'tx_container_parent' => 1,
                    'sys_language_uid' => 0,

                ],
            ],
        ];
        $modDatamap = $dataHandlerHook->_call('datamapForChildLocalizations', $datamap);
        self::assertIsArray($modDatamap['tt_content'][3]);
        self::assertSame(1, $modDatamap['tt_content'][3]['tx_container_parent']);
    }
}
