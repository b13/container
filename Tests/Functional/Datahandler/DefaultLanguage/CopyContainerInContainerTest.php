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

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class CopyContainerInContainerTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/copy_container_in_container.xml');
    }

    /**
     * @test
     */
    public function copyContainerWithChildContainersCopiesContentInChildContainersIntoCorrectContainer(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
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
        $copiedChildContainer1 = $this->fetchOneRecord('t3_origuid', 2);
        $copiedContentInChildContainer1 = $this->fetchOneRecord('t3_origuid', 3);
        $copiedChildContainer2 = $this->fetchOneRecord('t3_origuid', 4);
        $copiedContentInChildContainer2 = $this->fetchOneRecord('t3_origuid', 5);
        self::assertSame($copiedChildContainer1['uid'], $copiedContentInChildContainer1['tx_container_parent']);
        self::assertSame($copiedChildContainer2['uid'], $copiedContentInChildContainer2['tx_container_parent']);
    }
}
