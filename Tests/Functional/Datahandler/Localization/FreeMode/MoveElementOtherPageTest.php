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

class MoveElementOtherPageTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function moveChildElementOutsideContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                52 => [
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
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/MoveChildElementOutsideContainerAtTopResult.csv');
    }

    /**
     * @test
     */
    public function moveChildElementOutsideContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -64,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 1,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/MoveChildElementOutsideContainerAfterElementResult.csv');
    }

    /**
     * @test
     */
    public function moveChildElementToOtherColumnTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => '61-201',
                            'sys_language_uid' => 1,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/MoveChildElementToOtherColumnTopResult.csv');
    }

    /**
     * @test
     */
    public function moveChildElementToOtherColumnAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -63,
                        'update' => [
                            'colPos' => '61-201',
                            'sys_language_uid' => 1,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/MoveChildElementToOtherColumnAfterElementResult.csv');
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                54 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => '61-201',
                            'sys_language_uid' => 1,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/MoveElementIntoContainerAtTopResult.csv');
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                54 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -63,
                        'update' => [
                            'colPos' => '61-201',
                            'sys_language_uid' => 1,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementOtherPage/MoveElementIntoContainerAfterElementResult.csv');
    }
}
