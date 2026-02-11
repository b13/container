<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\Localization;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CopyToLanguageSortingTest extends AbstractDatahandler
{
    public static function localizeKeepsSortingDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    4 => ['copyToLanguage' => 1],
                    1 => ['copyToLanguage' => 1],
                ],
            ], 'dataset' => 'Dataset1'],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['copyToLanguage' => 1],
                    4 => ['copyToLanguage' => 1],
                ],
            ], 'dataset' => 'Dataset2'],
        ];
    }

    #[Test]
    #[DataProvider('localizeKeepsSortingDataProvider')]
    public function localizeKeepsSorting(array $cmdmap, string $dataset): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeKeepsSorting.csv');
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeKeepsSorting' . $dataset . 'Result.csv');
    }

    #[Test]
    public function localizeChildAtTopOfContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeChildAtTopOfContainer.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copyToLanguage' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeChildAtTopOfContainerResult.csv');
    }

    #[Test]
    public function localizeChildAfterContainerChild(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/localize_child_after_child.csv');
        $cmdmap = [
            'tt_content' => [
                3 => [
                    'copyToLanguage' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeChildAfterContainerChildResult.csv');
    }

    #[Test]
    public function localizeWithNestedElements(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeWithNestedElements.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copyToLanguage' => 4,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeWithNestedElementsResult.csv');
    }

    #[Test]
    public function localizeWithMultipleNestedElements(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeWithMultipleNestedElements.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copyToLanguage' => 4,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeWithMultipleNestedElementsResult.csv');
    }
}
