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

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class MaxItemsTest extends AbstractDatahandler
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
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 11) {
            // content_defender calls FormDataCompiler which wants access global variable TYPO3_REQUEST
            $GLOBALS['TYPO3_REQUEST'] = null;
        }
        $this->linkSiteConfigurationIntoTestInstance();
    }

    /**
     * @test
     * @group content_defender
     */
    public function canMoveElementIntoContainerIfMaxitemsIsNotReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_move_element_into_container_if_maxitems_is_not_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/cannot_move_element_into_container_if_maxitems_is_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/cannot_copy_element_into_container_if_maxitems_is_reached.csv');

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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/cannot_copy_element_into_container_if_maxitems_is_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_create_element_in_container_if_maxitems_is_not_reached.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'colPos' => 202,
                    'tx_container_parent' => 1,
                    'pid' => 1,
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/cannot_create_element_in_container_if_maxitems_is_reached.csv');
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
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canEditElementInContainerWhenMaxitemIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_edit_element_in_container_if_maxitems_is_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_move_container_with_maxitems_reached_column_to_other_page.csv');
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
    public function canCopyContainerWithMaxitemsReachedColumnToOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_copy_container_with_maxitems_reached_column_to_other_page.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Maxitems/CanCopyContainerWithMaxitemsReachedColumnToOtherPageResult.csv');
        self::assertSame([], $this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyElementFromContainerMaxitemsReachedColumnToOtherColumn(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_copy_element_from_container_maxitems_reached_column_to_other_column.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_copy_element_from_container_maxitems_reached_column_to_other_container.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_copy_element_from_container_maxitems_reached_column_to_other_container.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/cannot_move_element_inside_container_column_if_maxitems_is_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_translate_child_if_container_of_default_language_maxitems_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_copy_to_language_child_if_container_of_default_language_maxitems_reached.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Maxitems/can_save_child_in_default_language_when_translated_and_maxitems_is_reached.csv');
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
}
