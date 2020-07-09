<?php
namespace B13\Container\Tests\Functional\Datahandler\Localization;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class LocalizeTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
    }

    /**
     * @test
     */
    public function copyToLanguageContainerCopiesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copyToLanguage' => 1
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 2);
        $translatedContainerRow = $this->fetchOneRecord('t3_origuid', 1);
        $this->assertSame($translatedContainerRow['uid'], $translatedChildRow['tx_container_parent']);
        $this->assertSame(200, $translatedChildRow['colPos']);
        $this->assertSame(1, $translatedChildRow['pid']);
        $this->assertSame(0, $translatedChildRow['l18n_parent']);
    }

    /**
     * @test
     */
    public function localizeContainerLocalizeChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'localize' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 2);
        $this->assertSame(1, $translatedChildRow['tx_container_parent']);
        $this->assertSame(200, $translatedChildRow['colPos']);
        $this->assertSame(1, $translatedChildRow['pid']);
        $this->assertSame(2, $translatedChildRow['l18n_parent']);
    }
}
