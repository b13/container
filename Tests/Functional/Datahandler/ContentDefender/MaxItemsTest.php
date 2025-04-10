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

use TYPO3\CMS\Core\Utility\StringUtility;

class MaxItemsTest extends AbstractContentDefender
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
        $this->linkSiteConfigurationIntoTestInstance();
    }

    /**
     * @test
     * @group content_defender
     */
    public function canMoveElementIntoContainerIfMaxitemsIsNotReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanMoveElementIntoContainerIfMaxitemsIsNotReached.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanMoveElementIntoContainerIfMaxitemsIsNotReachedResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotMoveElementIntoContainerIfMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotMoveElementIntoContainerIfMaxitemsIsReached.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -3,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotMoveElementIntoContainerIfMaxitemsIsReachedResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCopyElementIntoContainerIfMaxitemsIsReachedAfterIntoContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotCopyElementIntoContainerIfMaxitemsIsReachedAfterIntoContainer.csv');

        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        // into container

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotCopyElementIntoContainerIfMaxitemsIsReachedAfterIntoContainerResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCopyElementIntoContainerIfMaxitemsIsReachedAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotCopyElementIntoContainerIfMaxitemsIsReachedAfterElement.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => -2,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotCopyElementIntoContainerIfMaxitemsIsReachedAfterElementResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCreateElementInContainerIfMaxitemsIsNotReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCreateElementInContainerIfMaxitemsIsNotReached.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'colPos' => '202',
                    'tx_container_parent' => 1,
                    'pid' => -1,
                    'sys_language_uid' => 0,
                    'header' => 'my-new-header',
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCreateElementInContainerIfMaxitemsIsNotReachedResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCreateElementInContainerIfMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotCreateElementInContainerIfMaxitemsIsReached.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'colPos' => 202,
                    'tx_container_parent' => 1,
                    'pid' => 1,
                    'sys_language_uid' => 0,
                    'header' => $newId,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotCreateElementInContainerIfMaxitemsIsReachedResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canEditElementInContainerWhenMaxitemIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanEditElementInContainerWhenMaxitemIsReached.csv');
        $datamap = [
            'tt_content' => [
                3 => [
                    'colPos' => 202,
                    'tx_container_parent' => 1,
                    'pid' => 1,
                    'sys_language_uid' => 0,
                    'header' => 'bar',
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanEditElementInContainerWhenMaxitemIsReachedResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canMoveContainerWithMaxitemsReachedColumnToOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanMoveContainerWithMaxitemsReachedColumnToOtherPage.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 2,
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanMoveContainerWithMaxitemsReachedColumnToOtherPageResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyElementFromContainerMaxitemsReachedColumnToOtherColumn(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyElementFromContainerMaxitemsReachedColumnToOtherColumn.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyElementFromContainerMaxitemsReachedColumnToOtherColumnResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyElementFromContainerMaxitemsReachedColumnToOtherContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyElementFromContainerMaxitemsReachedColumnToOtherContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '3-201',
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyElementFromContainerMaxitemsReachedColumnToOtherContainerResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canMoveElementFromContainerMaxitemsReachedColumnToOtherContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanMoveElementFromContainerMaxitemsReachedColumnToOtherContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '3-201',
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanMoveElementFromContainerMaxitemsReachedColumnToOtherContainerResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotMoveElementInsideContainerColumnIfMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CannotMoveElementInsideContainerColumnIfMaxitemsIsReached.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -2,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canTranslateChildIfContainerOfDefaultLanguageMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanTranslateChildIfContainerOfDefaultLanguageMaxitemsIsReached.csv');
        $cmdmap = [
            'tt_content' => [
                3 => ['localize' => 1],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanTranslateChildIfContainerOfDefaultLanguageMaxitemsIsReachedResult.csv');
        self::assertEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyToLanguageChildIfContainerOfDefaultLanguageMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyToLanguageChildIfContainerOfDefaultLanguageMaxitemsIsReached.csv');
        $cmdmap = [
            'tt_content' => [
                3 => ['copyToLanguage' => 1],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyToLanguageChildIfContainerOfDefaultLanguageMaxitemsIsReachedResult.csv');
        self::assertEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canSaveChildInDefaultLanguageWhenTranslatedAndMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanSaveChildInDefaultLanguageWhenTranslatedAndMaxitemsIsReached.csv');
        $record = [
           'uid' => 3,
           'pid' => 1,
           'colPos' => 202,
           'sorting' => 1024,
           'CType' => 'header',
           'tx_container_parent' => 1,
            'sys_language_uid' => 0,
        ];
        $datamap = [
            'tt_content' => [
                3 => $record,
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanSaveChildInDefaultLanguageWhenTranslatedAndMaxitemsIsReachedResult.csv');
        self::assertEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyFilledContainerWithMaxitemsReachedColumnToTopOfPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyFilledContainerWithMaxitemsReachedColumnToTopOfPageResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyChildFromFilledContainerFromMaxItemsReachedColumnToTopOfPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => 0,
                            'tx_container_parent' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyChildFromFilledContainerFromMaxItemsReachedColumnToTopOfPage.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyChildFromFilledContainerWhenCopyPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
        $cmdmap = [
            'pages' => [
                1 => [
                    'copy' => -1,
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyChildFromFilledContainerWhenCopyPage.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCopyChildFromFilledContainerIntoMaxItemsReachedColumnAfterChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
        $cmdmap = [
            'tt_content' => [
                3 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -2,
                        'update' => [
                            'colPos' => 200,
                            'tx_container_parent' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCopyChildFromFilledContainerIntoMaxItemsReachedColumnAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
        $cmdmap = [
            'tt_content' => [
                3 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => 200,
                            'tx_container_parent' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/filled_container.csv');
    }
}
