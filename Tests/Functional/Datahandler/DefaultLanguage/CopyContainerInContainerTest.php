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

class CopyContainerInContainerTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function copyContainerWithChildContainersCopiesContentInChildContainersIntoCorrectContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyContainerInContainer/CopyContainerWithChildContainersCopiesContentInChildContainersIntoCorrectContainer.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyContainerInContainer/CopyContainerWithChildContainersCopiesContentInChildContainersIntoCorrectContainerResult.csv');
    }
}
