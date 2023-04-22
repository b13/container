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

class CopyPageTest extends DatahandlerTest
{
    /**
     * @test
     */
    public function copyPageCopiesChildrenOfContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyPage/setup.csv');
        $cmdmap = [
            'pages' => [
                1 => [
                    'copy' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();

        $copiedRecord = $this->fetchOneRecord('t3_origuid', 1);
        $child = $this->fetchOneRecord('t3_origuid', 2);

        self::assertSame(2, $child['pid']);
        self::assertSame(2, $copiedRecord['pid']);

        self::assertSame(3, $copiedRecord['uid']);
        self::assertSame(4, $child['uid']);

        self::assertSame(3, $child['tx_container_parent']);
        self::assertSame(200, $child['colPos']);
        self::assertSame(0, $child['sys_language_uid']);
    }
}
