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

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class CopyElementOtherPageTest extends DatahandlerTest
{

    /**
     * @test
     */
    public function copyChildElementOutsideContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
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
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 2);
        self::assertSame(0, (int)$row['tx_container_parent']);
        self::assertSame(0, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyChildElementOutsideContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
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
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 2);
        self::assertSame(0, (int)$row['tx_container_parent']);
        self::assertSame(0, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyChildElementToOtherColumnTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 2);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
        $container = $this->fetchOneRecord('uid', 11);
        self::assertTrue($row['sorting'] > $container['sorting'], 'copied element is not sorted after container');
    }

    /**
     * @test
     */
    public function copyChildElementToOtherColumnAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -13,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 2);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyElementIntoContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 4);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);

        $container = $this->fetchOneRecord('uid', 11);
        self::assertTrue($row['sorting'] > $container['sorting'], 'copied element is not sorted after container');
    }

    /**
     * @test
     */
    public function copyElementIntoContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -13,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 4);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyElementIntoContainerAfterElementWithSimpleCommandMap(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        // see test above
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => -13,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 4);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyElementAfterContainerSortElementAfterLastContainerChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -11,
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
        $row = $this->fetchOneRecord('t3_origuid', 4);
        $lastChild = $this->fetchOneRecord('uid', 13);
        $nextElement = $this->fetchOneRecord('uid', 14);
        self::assertTrue($row['sorting'] > $lastChild['sorting'], 'copied element is not sorted after last child container');
        self::assertTrue($row['sorting'] < $nextElement['sorting'], 'copied element is not sorted before containers next element');
    }

    /**
     * @test
     */
    public function copyElementAfterContainerSortElementAfterLastContainerChildSimpleCommand(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => -11,
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 4);
        $lastChild = $this->fetchOneRecord('uid', 13);
        $nextElement = $this->fetchOneRecord('uid', 14);
        self::assertTrue($row['sorting'] > $lastChild['sorting'], 'copied element is not sorted after last child container');
        self::assertTrue($row['sorting'] < $nextElement['sorting'], 'copied element is not sorted before containers next element');
    }
}
