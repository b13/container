<?php
namespace B13\Container\Tests\Functional\Datahandler\Workspace;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class ContainerTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->backendUser->setWorkspace(1);
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
                ]
            ]
        ];

        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();

        // new container
        $row = $this->fetchOneRecord('t3ver_oid', 1);
        $this->assertSame(1, $row['t3ver_wsid']);
        // child
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertFalse($row);
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
                            'colPos' => 0
                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();

        $queryBuilder = $this->getQueryBuilder();
        $containerRow = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertIsArray($containerRow);
        $queryBuilder = $this->getQueryBuilder();
        $rows = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();
        foreach ($rows as $row) {
            $this->assertSame(1, $row['t3ver_wsid']);
            $this->assertSame($containerRow['uid'], $row['tx_container_parent']);
        }
    }

}
