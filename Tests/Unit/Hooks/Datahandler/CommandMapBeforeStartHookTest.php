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
        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            [
                'containerFactory' => $containerFactory->reveal(),
                'tcaRegistry' => null,
                'database' => null,
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
        $database = $this->prophesize(Database::class);
        $copyAfterRecord = [
            'uid' => 1,
            'tx_container_parent' => 2,
            'sys_language_uid' => 0,
            'colPos' => 3,
        ];
        $database->fetchOneRecord(1)->willReturn($copyAfterRecord);

        $dataHandlerHook = $this->getAccessibleMock(
            CommandMapBeforeStartHook::class,
            ['foo'],
            ['containerFactory' => null, 'tcaRegistry' => null, 'database' => $database->reveal()]
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
}
