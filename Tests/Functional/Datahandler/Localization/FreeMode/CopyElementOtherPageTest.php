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

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use PHPUnit\Framework\Attributes\Test;

class CopyElementOtherPageTest extends AbstractDatahandler
{
    #[Test]
    public function copyChildElementOutsideContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/CopyChildElementOutsideContainerAtTopResult.csv');
    }

    #[Test]
    public function copyChildElementOutsideContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/CopyChildElementOutsideContainerAfterElementResult.csv');
    }

    #[Test]
    public function copyChildElementToOtherColumnTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/CopyChildElementToOtherColumnTopResult.csv');
    }

    #[Test]
    public function copyChildElementToOtherColumnAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/CopyChildElementToOtherColumnAfterElementResult.csv');
    }

    #[Test]
    public function copyElementIntoContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/CopyElementIntoContainerAtTopResult.csv');
    }

    #[Test]
    public function copyElementIntoContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/setup.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElementOtherPage/CopyElementIntoContainerAfterElementResult.csv');
    }
}
