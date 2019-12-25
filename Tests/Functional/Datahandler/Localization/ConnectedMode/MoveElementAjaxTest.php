<?php
namespace B13\Container\Tests\Functional\Datahandler\Localization\ConnectedMode;

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class MoveElementAjaxTest extends DatahandlerTest
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
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_translations_connected_mode.xml');
    }

    /**
     * @test
     */
    public function moveChildElementOutsideContainerAtTop(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => 1
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => 0,
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 22);
        $this->assertSame(0, (int)$row['tx_container_parent']);
        $this->assertSame(0, (int)$row['colPos']);
        $this->assertSame(1, (int)$row['pid']);
        $this->assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveChildElementOutsideContainerAfterElement(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => -4
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => 0,
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 22);
        $this->assertSame(0, (int)$row['tx_container_parent']);
        $this->assertSame(0, (int)$row['colPos']);
        $this->assertSame(1, (int)$row['pid']);
        $this->assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveChildElementToOtherColumnTop(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => 1
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => '1-201',
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 22);
        $this->assertSame(1, (int)$row['tx_container_parent']);
        $this->assertSame(201, (int)$row['colPos']);
        $this->assertSame(1, (int)$row['pid']);
        $this->assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveChildElementToOtherColumnAfterElement(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'move' => -3
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => '1-201',
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 22);
        $this->assertSame(1, (int)$row['tx_container_parent']);
        $this->assertSame(201, (int)$row['colPos']);
        $this->assertSame(1, (int)$row['pid']);
        $this->assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAtTop(): void
    {
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => 1
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                4 => [
                    'colPos' => '1-201',
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 24);
        $this->assertSame(1, (int)$row['tx_container_parent']);
        $this->assertSame(201, (int)$row['colPos']);
        $this->assertSame(1, (int)$row['pid']);
        $this->assertSame(1, (int)$row['sys_language_uid']);
    }

    /**
     * @test
     */
    public function moveElementIntoContainerAfterElement(): void
    {
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'move' => -3
                ]
            ]
        ];
        $datamap = [
            'tt_content' => [
                4 => [
                    'colPos' => '1-201',
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, $cmdmap, $this->backendUser);
        $this->dataHandler->process_datamap();
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 24);
        $this->assertSame(1, (int)$row['tx_container_parent']);
        $this->assertSame(201, (int)$row['colPos']);
        $this->assertSame(1, (int)$row['pid']);
        $this->assertSame(1, (int)$row['sys_language_uid']);
    }
}
