<?php


namespace B13\Container\Tests\Functional\Datahandler\DefaultLanguage;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class MoveElementClipboardOtherPageTest extends DatahandlerTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language_other_page.xml');
    }

    /**
     * @test
     */
    public function moveChildElementOutsideContainerAtTop()
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
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
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(0, (int)$row['tx_container_parent']);
        self::assertSame(0, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveChildElementOutsideContainerAfterElement()
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -14,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(0, (int)$row['tx_container_parent']);
        self::assertSame(0, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveChildElementToOtherColumnTop()
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveChildElementToOtherColumnAfterElement()
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -13,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAtTop()
    {
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 4);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAfterElement()
    {
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -13,
                        'update' => [
                            'colPos' => '11-201',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 4);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAfterElementWithSimpleCommandMap()
    {
        // see test above what should be done
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => -13
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 4);
        self::assertSame(11, (int)$row['tx_container_parent']);
        self::assertSame(201, (int)$row['colPos']);
        self::assertSame(3, (int)$row['pid']);
        self::assertSame(0, (int)$row['sys_language_uid']);
    }
}
