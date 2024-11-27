<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\DefaultLanguage;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function moveContainerIntoItSelfsNestedAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerIntoItSelfsNestedAfterElement.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => -3,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerIntoItSelfsNestedAfterElementResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is empty');
    }

    /**
     * @test
     */
    public function moveContainerIntoItSelfsNestedAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerIntoItSelfsNestedAtTop.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 2,
                        'update' => [
                            'colPos' => '2-202',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerIntoItSelfsNestedAtTopResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is empty');
    }

    /**
     * @test
     */
    public function moveContainerIntoItSelfsAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerIntoItSelfsAtTop.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 2,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerIntoItSelfsAtTopResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is empty');
    }

    /**
     * @test
     */
    public function deleteContainerDeleteChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/DeleteContainerDeleteChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
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
    public function moveContainerAfterElementMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerAfterElementMovesChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -4,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerAfterElementMovesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function moveContainerToOtherPageAtTopMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageAtTopMovesChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageAtTopMovesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerToOtherPageAtTopCopiesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerToOtherPageAtTopCopiesChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerToOtherPageAtTopCopiesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerToOtherPageAfterElementCopiesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerToOtherPageAfterElementCopiesChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -14,
                        'update' => [
                            'colPos' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerToOtherPageAfterElementCopiesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function moveContainerToOtherPageAfterElementMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageAfterElementMovesChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -14,
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageAfterElementMovesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerKeepsSortingOfChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerKeepsSortingOfChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerKeepsSortingOfChildrenResult.csv');
    }

    /**
     * @test
     */
    public function moveContainerOtherPageOnTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerOtherPageOnTop.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => 3,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerOtherPageOnTopResult.csv');
    }

    /**
     * @test
     */
    public function moveContainerOtherPageAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerOtherPageAfterElement.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => -10,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerOtherPageAfterElementResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerOtherPageOnTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerOtherPageOnTop.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerOtherPageOnTopResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerOtherPageAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerOtherPageAfterElement.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -10,
                        'update' => [],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerOtherPageAfterElementResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerWithDataHandlerLoggingDisabled(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerWithDataHandlerLoggingDisabled.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -1,
                        'update' => [],
                    ],
                ],
            ],
        ];
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = false;
        $dataHandler->start([], $cmdmap, $this->backendUser);
        $dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/CopyContainerWithDataHandlerLoggingDisabledSysLogResult.csv');
    }
}
