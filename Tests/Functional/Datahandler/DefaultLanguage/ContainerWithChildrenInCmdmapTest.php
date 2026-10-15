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
use PHPUnit\Framework\Attributes\Test;

/**
 * container and its children are part of the same cmdmap,
 * e.g. "select all" and paste in the Record module (clipboard reverses the order)
 */
class ContainerWithChildrenInCmdmapTest extends AbstractDatahandler
{
    #[Test]
    public function copyContainerWithChildrenInCmdmapCopiesChildrenOnlyOnce(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ContainerWithChildrenInCmdmap/Container.csv');
        $cmdmap = [
            'tt_content' => [
                3 => ['copy' => 3],
                2 => ['copy' => 3],
                1 => ['copy' => 3],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/ContainerWithChildrenInCmdmap/CopyContainerWithChildrenInCmdmapCopiesChildrenOnlyOnceResult.csv');
    }

    #[Test]
    public function moveContainerWithChildrenInCmdmapKeepsChildrenInContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ContainerWithChildrenInCmdmap/Container.csv');
        $cmdmap = [
            'tt_content' => [
                3 => ['move' => 3],
                2 => ['move' => 3],
                1 => ['move' => 3],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/ContainerWithChildrenInCmdmap/MoveContainerWithChildrenInCmdmapKeepsChildrenInContainerResult.csv');
    }

    #[Test]
    public function copyNestedContainerWithChildrenInCmdmapCopiesChildrenOnlyOnce(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ContainerWithChildrenInCmdmap/NestedContainer.csv');
        $cmdmap = [
            'tt_content' => [
                3 => ['copy' => 3],
                2 => ['copy' => 3],
                1 => ['copy' => 3],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/ContainerWithChildrenInCmdmap/CopyNestedContainerWithChildrenInCmdmapCopiesChildrenOnlyOnceResult.csv');
    }
}
