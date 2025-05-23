<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\Workspace;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerTest extends AbstractDatahandler
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_workspace.csv');
        $this->backendUser->setWorkspace(1);
        $context = GeneralUtility::makeInstance(Context::class);
        $workspaceAspect = new WorkspaceAspect(1);
        $context->setAspect('workspace', $workspaceAspect);
    }

    /**
     * @test
     */
    public function deleteContainerDeleteChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeleteContainerDeleteChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DeleteContainerDeleteChildrenResult.csv');
    }

    /**
     * @test
     */
    public function newVersionDoesNotCreateNewVersionsOfChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewVersionDoesNotCreateNewVersionsOfChildren.csv');
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewVersionDoesNotCreateNewVersionsOfChildren.csv');
        $datamap = [
            'tt_content' => [
                1 => [
                    'header' => 'container-ws',
                ],
            ],
        ];

        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewVersionDoesNotCreateNewVersionsOfChildrenResult.csv');
    }

    /**
     * @test
     */
    public function moveChildsColPosInContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveChildsColPosInContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
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
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveChildsColPosInContainerResult.csv');
    }

    /**
     * @test
     */
    public function moveChildOutsideContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveChildOutsideContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 3,
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveChildOutsideContainerResult.csv');
    }

    /**
     * @test
     */
    public function moveChildsColPosInOtherContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveChildsColPosInOtherContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '91-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveChildsColPosInOtherContainerResult.csv');
    }

    /**
     * @test
     */
    public function copyChildsColPosInContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyChildsColPosInContainer.csv');
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
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyChildsColPosInContainerResult.csv');
    }

    /**
     * @test
     */
    public function copyChildOutsideContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyChildOutsideContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
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

        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyChildOutsideContainerResult.csv');
    }

    /**
     * @test
     */
    public function copyChildsColPosInOtherContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyChildsColPosInOtherContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '91-201',
                            'sys_language_uid' => 0,

                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyChildsColPosInOtherContainerResult.csv');
    }

    /**
     * @test
     */
    public function copyContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyContainer.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 3,
                        'update' => [
                            'colPos' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyContainerResult.csv');
    }

    /**
     * @test
     */
    public function moveRecordInColPosCreatesWorkspaceElementInContainer()
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MoveRecordInColPosCreatesWorkspaceElementInContainer.csv');
        $cmdmap = [
            'tt_content' => [
                5 => [
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
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/MoveRecordInColPosCreatesWorkspaceElementInContainerResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotCopyThisChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotCopyThisChild.csv');
        $cmdmap = [
            'tt_content' => [
                10 => [
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
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotCopyThisChildResult.csv');
    }

    /**
     * @test
     */
    public function deleteContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotDiscardThisChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeleteContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotDiscardThisChild.csv');
        $cmdmap = [
            'tt_content' => [
                10 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/DeleteContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotDiscardThisChildResult.csv');
    }
}
