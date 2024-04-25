<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\Localization\FreeMode;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;

class ContainerTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function deleteContainerDeleteChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/setup.csv');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/DeleteContainerDeleteChildrenResult.csv');
    }

    /**
     * @test
     */
    public function moveContainerToOtherPageMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/setup.csv');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 1,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageMovesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerCopiesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/setup.csv');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerCopiesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerToOtherLanguageCopiesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/setup.csv');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerToOtherLanguageCopiesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerToOtherLanguageCopiesNestedChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/setup.csv');
        $cmdmap = [
            'tt_content' => [
                55 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerToOtherLanguageCopiesNestedChildrenResult.csv');
    }

    /**
     * @test
     */
    public function moveContainerToOtherLanguageMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/setup.csv');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherLanguageMovesChildrenResult.csv');
    }
}
