<?php
namespace B13\Container\Tests\Functional\Datahandler\Workspace;

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class ContainerTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->backendUser->setWorkspace(1);
    }

    /**
     * @test
     */
    public function newVersionCreateNewVersionOfChilds(): void
    {
        $datamap = [
            'tt_content' => [
                1 => [
                    'sys_language_uid' => '0',
                    'CType' => 'b13-2cols-with-header-container',
                    'header' => 'container-ws',
                    'tx_container_parent' => 0,
                    'colPos' => 0
                ]
            ]
        ];

        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        // new container
        $row = $this->fetchOneRecord('t3ver_oid', 1);
        // child
        $childRow = $this->fetchOneRecord('t3ver_oid', 2);
        $this->assertSame(1, $childRow['t3ver_wsid']);
        $this->assertSame($row['uid'], $childRow['tx_container_parent']);
        //
    }

}
