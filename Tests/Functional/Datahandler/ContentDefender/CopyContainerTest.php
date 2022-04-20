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

class CopyContainerTest extends DatahandlerTest
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
        'typo3conf/ext/content_defender',
    ];

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Datahandler/ContentDefender/Fixtures/copy_container.xml');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyContainerAfterElementCopiesChildEvenChildIsNotAllowedByContentDefenderInBackendLayout(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -3,
                        'update' => [
                            'colPos' => 9,
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $this->fetchOneRecord('t3_origuid', 2);
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyContainerIntoOtherContainerWithSameColPosCopiesAlsoChildEvenChildIsDisallowedInTargetContainer(): void
    {
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -2,
                        'update' => [
                            'colPos' => '1-200',
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('t3_origuid', 4);
        self::assertSame(1, (int)$row['tx_container_parent'], 'element is not copied into container');
        self::assertSame(200, (int)$row['colPos'], 'element is not copied into container colPos');
        $child = $this->fetchOneRecord('t3_origuid', 5);
        self::assertSame($row['uid'], (int)$child['tx_container_parent'], 'child is not copied into copied container');
        self::assertSame(200, (int)$row['colPos'], 'child is not copied into container colPos');
    }
}
