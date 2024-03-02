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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerTest extends AbstractDatahandler
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content_container_with_child_in_workspace.csv');
        $cmdmap = [
            'tt_content' => [
                11 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('uid', 'deleted')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(12, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        self::assertFalse($row);
    }

    protected function getMovedWorkspaceRows(int $movedUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $stm = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter($movedUid, Connection::PARAM_INT)
                )
            );
        $rows = $stm->executeQuery()->fetchAllAssociative();
        self::assertSame(1, count($rows));
        return $rows;
    }

    protected function getCopiedWorkspaceRows(int $copiedUid): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $rows = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter($copiedUid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
        self::assertSame(1, count($rows));
        return $rows;
    }

    /**
     * @test
     */
    public function newVersionDoesNotCreateNewVersionsOfChildren(): void
    {
        $datamap = [
            'tt_content' => [
                1 => [
                    'header' => 'container-ws',
                ],
            ],
        ];

        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();

        // new container
        $row = $this->fetchOneRecord('t3ver_oid', 1);
        self::assertSame(1, $row['t3ver_wsid']);
        // child
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(2, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        self::assertFalse($row);
    }

    /**
     * @test
     */
    public function moveChildsColPosInContainer(): void
    {
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

        // moved record is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $rows = $this->getMovedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(1, $row['pid']);
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame(1, $row['tx_container_parent']);
            self::assertSame(201, $row['colPos']);
        }
    }

    /**
     * @test
     */
    public function moveChildOutsideContainer(): void
    {
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

        // moved record is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $rows = $this->getMovedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame(0, $row['tx_container_parent']);
            self::assertSame(0, $row['colPos']);
            self::assertSame(3, $row['pid']);
        }
    }

    /**
     * @test
     */
    public function moveChildsColPosInOtherContainer(): void
    {
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

        // copied record is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $rows = $this->getMovedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(1, $row['pid']);
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame(91, $row['tx_container_parent']);
            self::assertSame(201, $row['colPos']);
        }
    }

    /**
     * @test
     */
    public function copyChildsColPosInContainer(): void
    {
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

        // moved record is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $rows = $this->getCopiedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(1, $row['pid']);
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame(1, $row['tx_container_parent']);
            self::assertSame(201, $row['colPos']);
        }
    }

    /**
     * @test
     */
    public function copyChildOutsideContainer(): void
    {
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

        // copied record is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $rows = $this->getCopiedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(3, $row['pid']);
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame(0, $row['tx_container_parent']);
            self::assertSame(0, $row['colPos']);
        }
    }

    /**
     * @test
     */
    public function copyChildsColPosInOtherContainer(): void
    {
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

        // copied record is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $rows = $this->getCopiedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(1, $row['pid']);
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame(91, $row['tx_container_parent']);
            self::assertSame(201, $row['colPos']);
        }
    }

    /**
     * @test
     */
    public function copyContainer(): void
    {
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

        // copied child is not modified
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, $row['tx_container_parent']);
        self::assertSame(200, $row['colPos']);

        $queryBuilder = $this->getQueryBuilder();
        $containerRow = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($containerRow);
        $rows = $this->getCopiedWorkspaceRows(2);
        foreach ($rows as $row) {
            self::assertSame(3, $row['pid']);
            self::assertSame(1, $row['t3ver_wsid']);
            self::assertSame($containerRow['uid'], $row['tx_container_parent']);
            self::assertSame(200, $row['colPos']);
        }
    }

    /**
     * @test
     */
    public function moveRecordInColPosCreatesWorkspaceElementInContainer()
    {
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
        $origFirstElement = $this->fetchOneRecord('uid', 2);
        $workspaceElement = $this->fetchOneRecord('t3ver_oid', 5);
        self::assertSame(1, $workspaceElement['tx_container_parent']);
        self::assertSame(200, $workspaceElement['colPos']);
        self::assertTrue($workspaceElement['sorting'] > $origFirstElement['sorting']);
    }

    /**
     * @test
     */
    public function copyContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotCopyThisChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content_deleted_placeholder.csv');
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

        $copiedContainer = $this->fetchOneRecord('t3_origuid', 10);
        //no children

        $queryBuilder = $this->getQueryBuilder();
        $possibleChild = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tx_container_parent',
                    $queryBuilder->createNamedParameter($copiedContainer['uid'], Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        self::assertFalse($possibleChild);
    }

    /**
     * @test
     */
    public function deleteContainerWithChildHasDeletedPlaceholderInWorkspaceDoNotDiscardThisChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content_deleted_placeholder.csv');
        $cmdmap = [
            'tt_content' => [
                10 => [
                    'delete' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        // deleted placeholder exists
        $this->fetchOneRecord('uid', 12);
    }
}
