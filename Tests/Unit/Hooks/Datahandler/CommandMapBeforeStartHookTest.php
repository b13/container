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
use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Tca\Registry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CommandMapBeforeStartHookTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function rewriteCommandMapTargetForTopAtContainerTest(): void
    {
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = new Container([], []);
        $containerFactory->buildContainer(3)->willReturn($container);
        $containerService = $this->prophesize(ContainerService::class);
        $containerService->getNewContentElementAtTopTargetInColumn($container, 2)->willReturn(-4);
        $database = $this->prophesize(Database::class);
        $tcaRegistry = $this->prophesize(Registry::class);
        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            [
                'containerFactory' => $containerFactory->reveal(),
                'tcaRegistry' => $tcaRegistry->reveal(),
                'database' => $database->reveal(),
                'containerService' => $containerService->reveal(), ]
        );
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => 2,
                            'tx_container_parent' => 3,
                        ],
                    ],
                ],
            ],
        ];
        // should be
        $expected = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -4,
                        'update' => [
                            'colPos' => 2,
                            'tx_container_parent' => 3,
                        ],
                    ],
                ],
            ],
        ];
        $rewrittenCommandMap = $dataHandlerHook->_call('rewriteCommandMapTargetForTopAtContainer', $cmdmap);
        self::assertSame($expected, $rewrittenCommandMap);
    }

    /**
     * @test
     */
    public function rewriteSimpleCommandMapTest(): void
    {
        $copyAfterRecord = [
            'uid' => 1,
            'tx_container_parent' => 2,
            'sys_language_uid' => 0,
            'colPos' => 3,
        ];

        $containerFactory = $this->prophesize(ContainerFactory::class);
        $containerService = $this->prophesize(ContainerService::class);
        $database = $this->prophesize(Database::class);
        $tcaRegistry = $this->prophesize(Registry::class);
        $database->fetchOneRecord(1)->willReturn($copyAfterRecord);
        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            ['containerFactory' => $containerFactory->reveal(), 'tcaRegistry' => $tcaRegistry->reveal(), 'database' => $database->reveal(), 'containerService' => $containerService->reveal()]
        );
        $commandMap = [
            'tt_content' => [
                4 => [
                    'copy' => -1,
                ],
            ],
        ];
        // should be
        $expected = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -1,
                        'update' => [
                            'colPos' => '2-3',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $rewrittenCommandMap = $dataHandlerHook->_call('rewriteSimpleCommandMap', $commandMap);
        self::assertSame($expected, $rewrittenCommandMap);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToSplittedColPosValue(): void
    {
        $database = $this->prophesize(Database::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $tcaRegistry = $this->prophesize(Registry::class);
        $containerService = $this->prophesize(ContainerService::class);
        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            ['containerFactory' => $containerFactory->reveal(), 'tcaRegistry' => $tcaRegistry->reveal(), 'database' => $database->reveal(), 'containerService' => $containerService->reveal()]
        );
        $commandMap = [
            'tt_content' => [
                39 => [
                    'copy' => [
                        'update' => [
                            'colPos' => '2-34',
                        ],
                    ],
                ],
            ],
        ];
        // should be
        $expected = [
            'tt_content' => [
                39 => [
                    'copy' => [
                        'update' => [
                            'colPos' => 34,
                            'tx_container_parent' => 2,

                        ],
                    ],
                ],
            ],
        ];
        $commandMap = $dataHandlerHook->_call('extractContainerIdFromColPosOnUpdate', $commandMap);
        self::assertSame(34, $commandMap['tt_content'][39]['copy']['update']['colPos']);
        self::assertSame(2, $commandMap['tt_content'][39]['copy']['update']['tx_container_parent']);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToZeroValue(): void
    {
        $database = $this->prophesize(Database::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $tcaRegistry = $this->prophesize(Registry::class);
        $containerService = $this->prophesize(ContainerService::class);
        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            ['containerFactory' => $containerFactory->reveal(), 'tcaRegistry' => $tcaRegistry->reveal(), 'database' => $database->reveal(), 'containerService' => $containerService->reveal()]
        );
        $commandMap = [
            'tt_content' => [
                39 => [
                    'copy' => [
                        'update' => [
                            'colPos' => '34',
                        ],
                    ],
                ],
            ],
        ];
        // should be
        $expected = [
            'tt_content' => [
                39 => [
                    'copy' => [
                        'update' => [
                            'colPos' => 34,
                            'tx_container_parent' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $commandMap = $dataHandlerHook->_call('extractContainerIdFromColPosOnUpdate', $commandMap);
        self::assertSame(34, $commandMap['tt_content'][39]['copy']['update']['colPos']);
        self::assertSame(0, $commandMap['tt_content'][39]['copy']['update']['tx_container_parent']);
    }
}
