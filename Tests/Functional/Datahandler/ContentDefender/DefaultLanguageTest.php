<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class DefaultLanguageTest extends AbstractContentDefender
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
        'typo3conf/ext/content_defender',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/setup.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function moveElementIntoContainerAtTopDoNotMoveDisallowedCTypeElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/disallowed_content_element.csv');
        $cmdmap = [
            'tt_content' => [
                71 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-200',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/MoveElementIntoContainerAtTopDoNotMoveDisallowedCTypeElementResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function moveElementIntoContainerAfterOtherElemenDoNotMoveDisallowedCTypeElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/disallowed_content_element.csv');
        $cmdmap = [
            'tt_content' => [
                71 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -2,
                        'update' => [
                            'colPos' => '1-200',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/MoveElementIntoContainerAfterOtherElemenDoNotMoveDisallowedCTypeElementResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyElementIntoContainerAtTopDoNotCopyDisallowedCTypeElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/disallowed_content_element.csv');
        $cmdmap = [
            'tt_content' => [
                71 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-200',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/CopyElementIntoContainerAtTopDoNotCopyDisallowedCTypeElementResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function moveElementIntoContainerAtTopMoveAisallowedCTypeElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/allowed_content_element.csv');
        $cmdmap = [
            'tt_content' => [
                71 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-200',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/MoveElementIntoContainerAtTopMoveAisallowedCTypeElementResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function moveElementIntoContainerAfterOtherElemenMoveAllowedCTypeElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/allowed_content_element.csv');
        $cmdmap = [
            'tt_content' => [
                71 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -2,
                        'update' => [
                            'colPos' => '1-200',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/MoveElementIntoContainerAfterOtherElemenMoveAllowedCTypeElementResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyElementIntoContainerAtTopCopyAllowedCTypeElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/allowed_content_element.csv');
        $cmdmap = [
            'tt_content' => [
                71 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-200',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/CopyElementIntoContainerAtTopCopyAllowedCTypeElementResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyChildFromOtherContainerIntoColposWhereTargetElementInOtherColposHasRestrictionIsAllowd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/CopyChildFromOtherContainerIntoColposWhereTargetElementInOtherColposHasRestrictionIsAllowed.csv');
        $cmdmap = [
            'tt_content' => [
                73 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -2,
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DefaultLanguage/CopyChildFromOtherContainerIntoColposWhereTargetElementInOtherColposHasRestrictionIsAllowdResult.csv');
    }
}
