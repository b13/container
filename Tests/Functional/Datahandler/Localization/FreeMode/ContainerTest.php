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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_translations_free_mode.xml');
    }

    /**
     * @test
     */
    public function deleteContainerDeleteChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'delete' => 1
                ]
            ]
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
    public function moveContainerAjaxToBottomMovesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'move' => -54
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                51 => [
                    'colPos' => '0',
                    'sys_language_uid' => 1

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $child = $this->fetchOneRecord('uid', 52);
        self::assertSame(1, $child['pid']);
        self::assertSame(51, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(1, $child['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveContainerClipboardToOtherPageMovesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 1

                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $child = $this->fetchOneRecord('uid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame(51, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(1, $child['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyClipboardCopiesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0
                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $copiedRecord = $this->fetchOneRecord('t3_origuid', 51);
        $child = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame($copiedRecord['uid'], $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(1, $child['sys_language_uid']);
    }

    /**
     * @test
     */
    public function copyClipboardToOtherLanguageCopiesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0
                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $copiedRecord = $this->fetchOneRecord('t3_origuid', 51);
        $child = $this->fetchOneRecord('t3_origuid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame($copiedRecord['uid'], $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(0, $child['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveContainerClipboardToOtherLanguageMovesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $child = $this->fetchOneRecord('uid', 52);
        self::assertSame(3, $child['pid']);
        self::assertSame(51, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(0, $child['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveContainerAjaxToOtherLanguageMovesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'move' => 1
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                51 => [
                    'colPos' => '0',
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $child = $this->fetchOneRecord('uid', 52);
        self::assertSame(1, $child['pid']);
        self::assertSame(51, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(0, $child['sys_language_uid']);
    }
}
