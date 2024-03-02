<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\Localization\ConnectedMode;

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
    public function deleteContainerDeleteTranslatedChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/delete_container.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 21);
        self::assertSame(1, $row['deleted']);
        $row = $this->fetchOneRecord('uid', 22);
        self::assertSame(1, $row['deleted']);
    }

    /**
     * @test
     */
    public function moveContainerToOtherPageMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/move_container_to_other_page.csv');
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
        $child = $this->fetchOneRecord('uid', 22);
        self::assertSame(3, $child['pid']);
        self::assertSame(1, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(1, $child['sys_language_uid']);
    }
}
