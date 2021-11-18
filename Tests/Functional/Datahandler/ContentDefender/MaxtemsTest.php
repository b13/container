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

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;
use TYPO3\CMS\Core\Utility\StringUtility;

class MaxtemsTest extends DatahandlerTest
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
        'typo3conf/ext/content_defender'
    ];

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @group content_defender
     */
    public function canMoveElementIntoContainerIfMaxitemsIsNotReached(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/ContentDefender/Fixtures/Maxitems/can_move_element_into_container_if_maxitems_is_not_reached.xml');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(1, (int)$row['tx_container_parent'], 'element is not in container');
        self::assertSame(202, (int)$row['colPos'], 'element has wrong colPos');
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotMoveElementIntoContainerIfMaxitemsIsReached(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/ContentDefender/Fixtures/Maxitems/cannot_move_element_into_container_if_maxitems_is_reached.xml');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => [
                        'action' => 'paste',
                        'target' => -3,
                        'update' => [
                            'colPos' => '1-202',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 2);
        self::assertSame(0, (int)$row['tx_container_parent'], 'element is moved into container');
        self::assertSame(0, (int)$row['colPos'], 'element is moved into container colPos');
    }

    /**
     * @test
     * @group content_defender
     */
    public function canCreateElementInContainerIfMaxitemsIsNotReached(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/ContentDefender/Fixtures/Maxitems/can_create_element_in_container_if_maxitems_is_not_reached.xml');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'colPos' => 202,
                    'tx_container_parent' => 1,
                    'pid' => 1,
                    'sys_language_uid' => 0,
                    'header' => $newId
                ]
            ]
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'header',
                    $queryBuilder->createNamedParameter($newId)
                )
            )
            ->execute()
            ->fetch();
        self::assertIsArray($row);
    }

    /**
     * @test
     * @group content_defender
     */
    public function cannotCreateElementInContainerIfMaxitemsIsReached(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/ContentDefender/Fixtures/Maxitems/cannot_create_element_in_container_if_maxitems_is_reached.xml');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'colPos' => 202,
                    'tx_container_parent' => 1,
                    'pid' => 1,
                    'sys_language_uid' => 0,
                    'header' => $newId
                ]
            ]
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'header',
                    $queryBuilder->createNamedParameter($newId)
                )
            )
            ->execute()
            ->fetch();
        self::assertFalse($row);
    }
}
