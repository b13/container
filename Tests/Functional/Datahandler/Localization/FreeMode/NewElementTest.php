<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\Localization\FreeMode;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use TYPO3\CMS\Core\Utility\StringUtility;

class NewElementTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function newElementAfterContainerSortElementAfterLastChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewElement/setup.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -1,
                    'sys_language_uid' => 1,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterContainerSortElementAfterLastChildResult.csv');
    }
}
