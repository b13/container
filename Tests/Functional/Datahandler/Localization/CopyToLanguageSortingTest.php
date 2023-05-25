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

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class CopyToLanguageSortingTest extends DatahandlerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->linkSiteConfigurationIntoTestInstance();
    }

    /**
     * @return array
     */
    public function localizeKeepsSortingDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    4 => ['copyToLanguage' => 1],
                    1 => ['copyToLanguage' => 1],
                ],
            ]],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['copyToLanguage' => 1],
                    4 => ['copyToLanguage' => 1],
                ],
            ]],
        ];
    }

    /**
     * @test
     * @dataProvider localizeKeepsSortingDataProvider
     */
    public function localizeKeepsSorting(array $cmdmap): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/localize_containers.csv');
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedContainer1 = $this->fetchOneRecord('t3_origuid', 1);
        $translatedChild11 = $this->fetchOneRecord('t3_origuid', 2);
        $translatedChild12 = $this->fetchOneRecord('t3_origuid', 3);
        $translatedContainer2 = $this->fetchOneRecord('t3_origuid', 4);
        $translatedChild21 = $this->fetchOneRecord('t3_origuid', 5);
        self::assertTrue($translatedContainer1['sorting'] < $translatedChild11['sorting'], 'child-1-1 is sorted before container-1');
        self::assertTrue($translatedChild11['sorting'] < $translatedChild12['sorting'], 'child-1-2 is sorted before child-1-1');
        self::assertTrue($translatedChild12['sorting'] < $translatedContainer2['sorting'], 'container-2 is sorted before child-1-2');
        self::assertTrue($translatedContainer2['sorting'] < $translatedChild21['sorting'], 'child-2-1 is sorted before container-2');
    }

    /**
     * @test
     */
    public function localizeChildAtTopOfContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/localize_child_at_top.csv');
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copyToLanguage' => 1,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedContainer1 = $this->fetchOneRecord('uid', 4);
        $translatedChild11 = $this->fetchOneRecord('t3_origuid', 2);
        $translatedChild12 = $this->fetchOneRecord('uid', 5);
        self::assertTrue($translatedContainer1['sorting'] < $translatedChild11['sorting'], 'child-1-1 is sorted before container-1');
        self::assertTrue($translatedChild11['sorting'] < $translatedChild12['sorting'], 'child-1-1 is sorted after child-1-2');
    }

    /**
     * @test
     */
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
        $translatedContainer1 = $this->fetchOneRecord('uid', 4);
        $translatedChild11 = $this->fetchOneRecord('uid', 5);
        $translatedChild12 = $this->fetchOneRecord('t3_origuid', 3);
        self::assertTrue($translatedContainer1['sorting'] < $translatedChild11['sorting'], 'child-1-1 is sorted before container-1');
        self::assertTrue($translatedChild11['sorting'] < $translatedChild12['sorting'], 'child-1-1 is sorted after child-1-2');
    }
}
