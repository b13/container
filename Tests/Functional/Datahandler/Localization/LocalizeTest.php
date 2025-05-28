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

class LocalizeTest extends AbstractDatahandler
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->linkSiteConfigurationIntoTestInstance();
    }

    /**
     * @test
     */
    public function copyChildToLanguageFixContainerParent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyChildToLanguageFixContainerParent.csv');
        $cmdmap = [
            'tt_content' => [
                72 => [
                    'copyToLanguage' => 1,
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyChildToLanguageFixContainerParentResult.csv');
    }

    /**
     * @test
     */
    public function copyContainerToLanguageCopiesChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyContainerToLanguageCopiesChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copyToLanguage' => 1,
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyContainerToLanguageCopiesChildrenResult.csv');
    }

    /**
     * @test
     */
    public function localizeContainerLocalizeChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeContainerLocalizeChildren.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'localize' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeContainerLocalizeChildrenResult.csv');
    }

    /**
     * @test
     */
    public function localizeNestedContainerKeepsDefaultLanguageParent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeNestedContainerKeepsDefaultLanguageParent.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'localize' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeNestedContainerKeepsDefaultLanguageParentResult.csv');
    }

    /**
     * @test
     */
    public function localizeContainerFromNonDefaultLanguageLocalizeChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeContainerFromNonDefaultLanguageLocalizeChildren.csv');
        $cmdmap = [
            'tt_content' => [
                21 => [
                    'localize' => 2,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeContainerFromNonDefaultLanguageLocalizeChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyToLanguageContainerFromNonDefaultLanguageLocalizeChildren(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyToLanguageContainerFromNonDefaultLanguageLocalizeChildren.csv');
        $cmdmap = [
            'tt_content' => [
                21 => [
                    'copyToLanguage' => 2,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyToLanguageContainerFromNonDefaultLanguageLocalizeChildrenResult.csv');
    }

    /**
     * @test
     */
    public function copyToLanguageContainerFromNonDefaultLanguageLocalizeChildrenWhenCopiedFromFreeMode(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyToLanguageContainerFromNonDefaultLanguageLocalizeChildrenWhenCopiedFromFreeMode.csv');
        $cmdmap = [
            'tt_content' => [
                51 => [
                    'copyToLanguage' => 2,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/CopyToLanguageContainerFromNonDefaultLanguageLocalizeChildrenWhenCopiedFromFreeModeResult.csv');
    }

    /**
     * @test
     */
    public function localizeChildFailedIfContainerIsInFreeMode(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeChildFailedIfContainerIsInFreeMode.csv');
        $cmdmap = [
            'tt_content' => [
                72 => [
                    'localize' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeChildFailedIfContainerIsInFreeModeResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log is empty');
    }

    /**
     * @test
     */
    public function localizeChildFailedIfContainerIsNotTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeChildFailedIfContainerIsNotTranslated.csv');
        $cmdmap = [
            'tt_content' => [
                72 => [
                    'localize' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeChildFailedIfContainerIsNotTranslatedResult.csv');
        self::assertNotEmpty($this->dataHandler->errorLog, 'dataHander error log should be empty');
    }

    /**
     * @test
     */
    public function localizeChildKeepsRelationsIfContainerIsInConnectedMode(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeChildKeepsRelationsIfContainerIsInConnectedMode.csv');
        $cmdmap = [
            'tt_content' => [
                82 => [
                    'localize' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeChildKeepsRelationsIfContainerIsInConnectedModeResult.csv');
    }

    /**
     * @return array
     */
    public static function localizeTwoContainerKeepsParentIndependedOnOrderDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    91 => ['localize' => 1],
                    1 => ['localize' => 1],
                ],
            ], 'Dataset1'],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['localize' => 1],
                    91 => ['localize' => 1],
                ],
            ], 'Dataset2'],
        ];
    }

    /**
     * @test
     * @dataProvider localizeTwoContainerKeepsParentIndependedOnOrderDataProvider
     */
    public function localizeTwoContainerKeepsParentIndependedOnOrder(array $cmdmap, string $dataset): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeTwoContainerKeepsParentIndependedOnOrder.csv');
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeTwoContainerKeepsParentIndependedOnOrder' . $dataset . 'Result.csv');
    }

    /**
     * @return array
     */
    public static function localizeWithCopyTwoContainerChangeParentIndependedOnOrderDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    91 => ['copyToLanguage' => 1],
                    1 => ['copyToLanguage' => 1],
                ],
            ], 'Dataset1'],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['copyToLanguage' => 1],
                    91 => ['copyToLanguage' => 1],
                ],
            ], 'Dataset2'],
        ];
    }

    /**
     * @test
     * @dataProvider localizeWithCopyTwoContainerChangeParentIndependedOnOrderDataProvider
     */
    public function localizeWithCopyTwoContainerChangeParentIndependedOnOrder(array $cmdmap, string $dataset): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeWithCopyTwoContainerChangeParentIndependedOnOrder.csv');
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeWithCopyTwoContainerChangeParentIndependedOnOrder' . $dataset . 'Result.csv');
    }

    /**
     * @test
     */
    public function localizeElementAfterAlreadyLocalizedContainerIsSortedAfterContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Localize/localize_element_after_already_localized_container.csv');
        $cmdmap = [
            'tt_content' => [3 => ['localize' => 1]],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Localize/LocalizeElementAfterAlreadyLocalizedContainerIsSortedAfterContainerResult.csv');
    }
}
