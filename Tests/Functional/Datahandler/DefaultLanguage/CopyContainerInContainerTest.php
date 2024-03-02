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

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;

class CopyContainerInContainerTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function copyContainerWithChildContainersCopiesContentInChildContainersIntoCorrectContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyContainerInContainer/setup.csv');
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
        $container = $this->fetchOneRecord('t3_origuid', 1);
        self::assertTrue($container['sorting'] < $copiedChildContainer1['sorting'], 'sorting fail 1');
        self::assertTrue($copiedChildContainer1['sorting'] < $copiedContentInChildContainer1['sorting'], 'sorting fail 2');
        self::assertTrue($copiedContentInChildContainer1['sorting'] < $copiedChildContainer2['sorting'], 'sorting fail 3');
        self::assertTrue($copiedChildContainer2['sorting'] < $copiedContentInChildContainer2['sorting'], 'sorting fail 4');
    }
}
