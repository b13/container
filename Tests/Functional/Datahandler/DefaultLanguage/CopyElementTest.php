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

class CopyElementTest extends AbstractDatahandler
{
    #[Test]
    public function copyChildElementOutsideContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyChildElementOutsideContainerAtTopResult.csv');
    }

    #[Test]
    public function copyChildElementOutsideContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -4,
                        'update' => [
                            'colPos' => 0,
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyChildElementOutsideContainerAfterElementResult.csv');
    }

    #[Test]
    public function copyChildElementToOtherColumnTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyChildElementToOtherColumnTopResult.csv');
    }

    #[Test]
    public function copyChildElementToOtherColumnAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -3,
                        'update' => [
                            'colPos' => '1-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyChildElementToOtherColumnAfterElementResult.csv');
    }

    #[Test]
    public function copyElementIntoContainerAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyElementIntoContainerAtTopResult.csv');
    }

    #[Test]
    public function copyElementIntoContainerAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -3,
                        'update' => [
                            'colPos' => '1-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyElementIntoContainerAfterElementResult.csv');
    }

    #[Test]
    public function copyElementIntoContainerAfterElementWithSimpleCommandMap(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        // see test above what should be done
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => -3,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyElementIntoContainerAfterElementWithSimpleCommandMapResult.csv');
    }

    #[Test]
    public function copyElementAfterContainerWithSimpleCommandMap(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        // see test above what should be done
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => -1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyElementAfterContainerWithSimpleCommandMapResult.csv');
    }

    #[Test]
    public function copyContainerIntoItSelfs(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => -2,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyContainerIntoItSelfsResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    #[Test]
    public function copyMultipleContainersWithChildRecords(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyElement/setup.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [],
                    ],
                ],
                6 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyElement/CopyMultipleContainersWithChildRecordsResult.csv');
    }
}
