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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CommandMapBeforeStartHookTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function rewriteCommandMapTargetForTopAtContainerTest(): void
    {
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildContainer'])
            ->getMock();
        $container = new Container([], []);
        $containerFactory->expects(self::once())->method('buildContainer')->with(3)->willReturn($container);
        $containerService = $this->getMockBuilder(ContainerService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getNewContentElementAtTopTargetInColumn'])
            ->getMock();
        $containerService->expects(self::once())->method('getNewContentElementAtTopTargetInColumn')->with($container, 2)->willReturn(-4);
        $database = $this->getMockBuilder(Database::class)->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(CommandMapBeforeStartHook::class))
            ->setConstructorArgs(['containerFactory' => $containerFactory, 'tcaRegistry' => $tcaRegistry, 'database' => $database, 'containerService' => $containerService])
            ->onlyMethods([])
            ->getMock();
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

    #[Test]
    public function rewriteSimpleCommandMapTestForIntoContainer(): void
    {
        $copyAfterRecord = [
            'uid' => 1,
            'tx_container_parent' => 2,
            'sys_language_uid' => 0,
            'colPos' => 3,
        ];

        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)->disableOriginalConstructor()->getMock();
        $database = $this->getMockBuilder(Database::class)->onlyMethods(['fetchOneRecord'])->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $database->expects(self::once())->method('fetchOneRecord')->with(1)->willReturn($copyAfterRecord);
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(CommandMapBeforeStartHook::class))
            ->setConstructorArgs(['containerFactory' => $containerFactory, 'tcaRegistry' => $tcaRegistry, 'database' => $database, 'containerService' => $containerService])
            ->onlyMethods([])
            ->getMock();
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

    #[Test]
    public function rewriteSimpleCommandMapTestForAfterContainer(): void
    {
        $copyAfterRecord = [
            'uid' => 1,
            'tx_container_parent' => 0,
            'sys_language_uid' => 0,
            'colPos' => 3,
            'CType' => 'container-ctype',
        ];

        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)->disableOriginalConstructor()->getMock();
        $database = $this->getMockBuilder(Database::class)->onlyMethods(['fetchOneRecord'])->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->onlyMethods(['isContainerElement'])->getMock();
        $database->expects(self::once())->method('fetchOneRecord')->with(1)->willReturn($copyAfterRecord);
        $tcaRegistry->expects(self::once())->method('isContainerElement')->with('container-ctype')->willReturn(true);
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(CommandMapBeforeStartHook::class))
            ->setConstructorArgs(['containerFactory' => $containerFactory, 'tcaRegistry' => $tcaRegistry, 'database' => $database, 'containerService' => $containerService])
            ->onlyMethods([])
            ->getMock();
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
                            'colPos' => 3,
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $rewrittenCommandMap = $dataHandlerHook->_call('rewriteSimpleCommandMap', $commandMap);
        self::assertSame($expected, $rewrittenCommandMap);
    }

    #[Test]
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToSplittedColPosValue(): void
    {
        $database = $this->getMockBuilder(Database::class)->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)->disableOriginalConstructor()->getMock();
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(CommandMapBeforeStartHook::class))
            ->setConstructorArgs(['containerFactory' => $containerFactory, 'tcaRegistry' => $tcaRegistry, 'database' => $database, 'containerService' => $containerService])
            ->onlyMethods([])
            ->getMock();
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

    #[Test]
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToSplittedColPosValueForDelimiterV12WithMultipleDelimiters(): void
    {
        $database = $this->getMockBuilder(Database::class)->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)->disableOriginalConstructor()->getMock();
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(CommandMapBeforeStartHook::class))
            ->setConstructorArgs(['containerFactory' => $containerFactory, 'tcaRegistry' => $tcaRegistry, 'database' => $database, 'containerService' => $containerService])
            ->onlyMethods([])
            ->getMock();
        $commandMap = [
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

    #[Test]
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToZeroValue(): void
    {
        $database = $this->getMockBuilder(Database::class)->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)->disableOriginalConstructor()->getMock();
        $dataHandlerHook = $this->getMockBuilder($this->buildAccessibleProxy(CommandMapBeforeStartHook::class))
            ->setConstructorArgs(['containerFactory' => $containerFactory, 'tcaRegistry' => $tcaRegistry, 'database' => $database, 'containerService' => $containerService])
            ->onlyMethods([])
            ->getMock();
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
