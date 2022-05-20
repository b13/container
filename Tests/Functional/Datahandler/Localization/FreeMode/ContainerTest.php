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

class ContainerTest extends DatahandlerTest
{

    /**
     * @test
     */
    public function deleteContainerDeleteChildren(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/Container/setup.xml');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 51);
        self::assertSame(1, $row['deleted']);
        $row = $this->fetchOneRecord('uid', 52);
        self::assertSame(1, $row['deleted']);
    }

    /**
     * @test
     */
    public function moveContainerToOtherPageMovesChildren(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/Container/setup.xml');
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
        $child = $this->fetchOneRecord('uid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame(51, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(1, $child['sys_language_uid']);
        $container = $this->fetchOneRecord('uid', 51);
        self::assertTrue($child['sorting'] > $container['sorting'], 'moved child is sorted before container');
    }

    /**
     * @test
     */
    public function copyContainerCopiesChildren(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/Container/setup.xml');
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
        $copiedRecord = $this->fetchOneRecord('t3_origuid', 51);
        $child = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame($copiedRecord['uid'], $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(1, $child['sys_language_uid']);
        $container = $this->fetchOneRecord('uid', 51);
        self::assertTrue($child['sorting'] > $container['sorting'], 'copied child is sorted before container');
    }

    /**
     * @test
     */
    public function copyContainerToOtherLanguageCopiesChildren(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/Container/setup.xml');
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
        $copiedRecord = $this->fetchOneRecord('t3_origuid', 51);
        $child = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame($copiedRecord['uid'], $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(0, $child['sys_language_uid']);
        self::assertTrue($child['sorting'] > $copiedRecord['sorting'], 'copied child is sorted before container');
    }

    /**
     * @test
     */
    public function copyContainerToOtherLanguageCopiesNestedChildren(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/Container/setup.xml');
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
        $child = $this->fetchOneRecord('t3_origuid', 56);
        $nestedChild = $this->fetchOneRecord('t3_origuid', 57);
        self::assertSame($child['uid'], $nestedChild['tx_container_parent']);
        self::assertSame(200, $nestedChild['colPos']);
        self::assertSame(0, $nestedChild['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveContainerToOtherLanguageMovesChildren(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/Localization/FreeMode/Fixtures/Container/setup.xml');
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
        $child = $this->fetchOneRecord('uid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame(51, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(0, $child['sys_language_uid']);
        $container = $this->fetchOneRecord('uid', 51);
        self::assertTrue($child['sorting'] > $container['sorting'], 'moved child is sorted before container');
    }
}
