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
    public function newVersionNotCreateNewVersionOfChilds(): void
    {
        $datamap = [
            'tt_content' => [
                1 => [
                    'header' => 'container-ws',
                ]
            ]
        ];

        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();
        
        // new container
        $row = $this->fetchOneRecord('t3ver_oid', 1);
        $this->assertSame(1, $row['t3ver_wsid']);
        // child
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3ver_oid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertFalse($row);
    }

}
