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
 * Regression tests for #738 — drop-at-top of a container child column places
 * the moved element outside the children's sort space when
 * container.sorting > min(child.sorting). The hook now normalises the
 * children's sorting before the -container_uid fallback anchor is used, so
 * the moved element lands at the top of the children as intended.
 */
class MoveElementSortingInvertedTest extends AbstractDatahandler
{
    #[Test]
    public function moveIntoInvertedContainerAtTopViaNegativeTarget(): void
    {
        // Path 2: cmd already carries target = -container_uid.
        // Upstream, the sorting rewrite skipped this branch because of the
        // `target > 0` gate, so the anchor reached DataHandler unchanged.
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementSortingInverted/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -1,
                        'update' => [
                            'colPos' => 200,
                            'tx_container_parent' => 1,
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementSortingInverted/MoveIntoInvertedContainerAtTopResult.csv');
    }

    #[Test]
    public function moveIntoInvertedContainerAtTopViaPositivePageTarget(): void
    {
        // Path 1: cmd arrives with target = positive page uid + update carries
        // tx_container_parent + colPos. The rewrite path resolves this via
        // ContainerService::getNewContentElementAtTopTargetInColumn(), which
        // falls back to -container_uid for the first configured column when
        // no preceding column has records.
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveElementSortingInverted/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => 200,
                            'tx_container_parent' => 1,
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveElementSortingInverted/MoveIntoInvertedContainerAtTopResult.csv');
    }
}
