<?php
namespace B13\Container\Tests\Functional\Datahandler\DefaultLanguage;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class CopyPageTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/copy_page.xml');
    }

    /**
     * @test
     */
    public function copyPageCopiesChildrenOfContainer(): void
    {

        $cmdmap = [
            'pages' => [
                1 => [
                    'copy' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();

        $copiedRecord = $this->fetchOneRecord('t3_origuid', 1);
        $child = $this->fetchOneRecord('t3_origuid', 2);

        $this->assertSame(2, $child['pid']);
        $this->assertSame(2, $copiedRecord['pid']);

        $this->assertSame(3, $copiedRecord['uid']);
        $this->assertSame(4, $child['uid']);

        $this->assertSame(3, $child['tx_container_parent']);
        $this->assertSame(200, $child['colPos']);
        $this->assertSame(0, $child['sys_language_uid']);

    }
}
