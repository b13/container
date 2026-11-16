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
use PHPUnit\Framework\Attributes\Test;

class ContainerTest extends AbstractDatahandler
{
    #[Test]
    public function deleteContainerDeleteTranslatedChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/DeleteContainerDeleteTranslatedChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/DeleteContainerDeleteTranslatedChildrenResult.csv');
    }

    /**
     * @see https://github.com/b13/container/issues/695
     */
    #[Test]
    public function hardDeleteDeletedContainerTranslationKeepsChildrenOfOtherTranslation(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/HardDeleteDeletedContainerTranslationKeepsChildrenOfOtherTranslation.csv');
        // same calls as in cleanup:deletedrecords
        $this->dataHandler->start([], [], $this->backendUser);
        $this->dataHandler->deleteAction('tt_content', 21, true, true);
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/HardDeleteDeletedContainerTranslationKeepsChildrenOfOtherTranslationResult.csv');
    }

    #[Test]
    public function moveContainerToOtherPageMovesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageMovesChildren.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Container/MoveContainerToOtherPageMovesChildrenResult.csv');
    }
}
