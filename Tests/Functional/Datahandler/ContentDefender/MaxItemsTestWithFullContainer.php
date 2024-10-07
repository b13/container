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

class MaxItemsTestWithFullContainer extends AbstractDatahandler
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
    public function cannotMoveElementIntoContainerIfMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/cannot_move_element_into_container_if_maxitems_is_reached.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -3,
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/CannotMoveElementIntoContainerIfMaxitemsIsReachedResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCopyElementIntoContainerIfMaxitemsIsReachedAfterIntoContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/cannot_copy_element_into_container_if_maxitems_is_reached.csv');

        $cmdmap = [
            'tt_content' => [
                2 => [
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
        // into container

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/CannotCopyElementIntoContainerIfMaxitemsIsReachedAfterIntoContainerResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCopyElementIntoContainerIfMaxitemsIsReachedAfterElement(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/cannot_copy_element_into_container_if_maxitems_is_reached.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/CannotCopyElementIntoContainerIfMaxitemsIsReachedAfterElementResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCreateElementInContainerIfMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/cannot_create_element_in_container_if_maxitems_is_reached.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'colPos' => 200,
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/CannotCreateElementInContainerIfMaxitemsIsReachedResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCopyContainerWithMaxitemsReachedColumnToOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/can_copy_container_with_maxitems_reached_column_to_other_page.csv');
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/CanCopyContainerWithMaxitemsReachedColumnToOtherPageResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotMoveElementInsideContainerColumnIfMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/cannot_move_element_inside_container_column_if_maxitems_is_reached.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
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
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canTranslateChildIfContainerOfDefaultLanguageMaxitemsIsReached(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/can_translate_child_if_container_of_default_language_maxitems_reached.csv');
        $cmdmap = [
            'tt_content' => [
                3 => ['localize' => 1],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MaxitemsWithFullContainer/CanTranslateChildIfContainerOfDefaultLanguageMaxitemsIsReachedResult.csv');
        self::assertEmpty($this->dataHandler->errorLog, 'dataHander error log is not empty');
    }
}
