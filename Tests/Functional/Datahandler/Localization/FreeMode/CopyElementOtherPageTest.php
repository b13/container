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

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class CopyElementOtherPageTest extends DatahandlerTest
{

    /**
     * @test
     */
    public function copyChildElementOutsideContainerAtTop(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/CopyElementOtherPage/setup.xml');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'copy' => [
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
        $row = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(0, (int)$row['tx_container_parent']);
        self::assertSame(0, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyChildElementOutsideContainerAfterElement(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/CopyElementOtherPage/setup.xml');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'copy' => [
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
        $row = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(0, (int)$row['tx_container_parent']);
        self::assertSame(0, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyChildElementToOtherColumnTop(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/CopyElementOtherPage/setup.xml');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'copy' => [
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
        $row = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(61, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(1, (int)$row['sys_language_uid']);
        $container = $this->fetchOneRecord('uid', 61);
        self::assertTrue($row['sorting'] > $container['sorting'], 'copied element is not sorted after container');
    }

    /**
     * @test
     */
    public function copyChildElementToOtherColumnAfterElement(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/CopyElementOtherPage/setup.xml');
        $cmdmap = [
            'tt_content' => [
                52 => [
                    'copy' => [
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
        $row = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(61, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyElementIntoContainerAtTop(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/CopyElementOtherPage/setup.xml');
        $cmdmap = [
            'tt_content' => [
                54 => [
                    'copy' => [
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
        $row = $this->fetchOneRecord('t3_origuid', 54);
        self::assertSame(61, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(1, (int)$row['sys_language_uid']);
        $container = $this->fetchOneRecord('uid', 61);
        self::assertTrue($row['sorting'] > $container['sorting'], 'copied element is not sorted after container');
    }

    /**
     * @test
     */
    public function copyElementIntoContainerAfterElement(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/CopyElementOtherPage/setup.xml');
        $cmdmap = [
            'tt_content' => [
                54 => [
                    'copy' => [
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
        $row = $this->fetchOneRecord('t3_origuid', 54);
        self::assertSame(61, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(1, (int)$row['sys_language_uid']);
    }
}
