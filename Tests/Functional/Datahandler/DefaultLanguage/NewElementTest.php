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
use TYPO3\CMS\Core\Utility\StringUtility;

class NewElementTest extends AbstractDatahandler
{
    /**
     * @test
     */
    public function newElementAfterContainerSortElementAfterLastChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterContainerSortElementAfterLastChild.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -1,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterContainerSortElementAfterLastChildResult.csv');
    }

    /**
     * @test
     */
    public function newElementAfterNestedContainerSortElementAfterLastChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterNestedContainerSortElementAfterLastChild.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -3,
                    'colPos' => 201,
                    'tx_container_parent' => 2,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterNestedContainerSortElementAfterLastChildResult.csv');
    }

    /**
     * @test
     */
    public function newElementAtTop(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAtTop.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -1,
                    'CType' => 'header',
                    'header' => 'child-at-top',
                    'colPos' => 200,
                    'tx_container_parent' => 1,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAtTopResult.csv');
    }

    /**
     * @test
     */
    public function newElementAfterChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterChild.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -2,
                    'CType' => 'header',
                    'header' => 'child-after-child',
                    'colPos' => 200,
                    'tx_container_parent' => 1,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementAfterChildResult.csv');
    }

    /**
     * @test
     */
    public function newElementInNexCol(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementInNextCol.csv');
        $newId = StringUtility::getUniqueId('NEW');
        $datamap = [
            'tt_content' => [
                $newId => [
                    'pid' => -2,
                    'CType' => 'header',
                    'header' => 'child-in-next-col',
                    'colPos' => 201,
                    'tx_container_parent' => 1,
                ],
            ],
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/NewElement/NewElementInNextColResult.csv');
    }
}
