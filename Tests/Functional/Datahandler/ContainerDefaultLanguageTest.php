<?php
namespace B13\Container\Tests\Functional\Datahandler;

class ContainerDefaultLanguageTest extends DatahandlerTest
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
    public function deleteContainerByCmdmapDeleteChilds(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'delete' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $row = $this->fetchOneRecord('uid', 1);
        $this->assertSame(1, $row['deleted']);
        $row = $this->fetchOneRecord('uid', 2);
        $this->assertSame(1, $row['deleted']);
    }
}
