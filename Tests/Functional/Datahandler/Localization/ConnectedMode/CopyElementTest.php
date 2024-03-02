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

class CopyElementTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function copyElementAfterContainerCopiesTranslationAfterContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/copy_element_after_container.csv');
        $cmdmap = [
            'tt_content' => [
                3 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -1,
                        'update' => [],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();

        $translatedRow = $this->fetchOneRecord('t3_origuid', 4);
        self::assertTrue($translatedRow['sorting'] > 512);
    }

    /**
     * @test
     */
    public function copyElementAfterContainerWithChildKeepsColPosForTranslatedElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/copy_element_after_container_with_child.csv');
        $cmdmap = [
            'tt_content' => [
                3 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -1,
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
        $translatedRow = $this->fetchOneRecord('t3_origuid', 4);
        self::assertTrue($translatedRow['colPos'] === 0);
    }
}
