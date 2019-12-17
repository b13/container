<?php
namespace B13\Container\Tests\Functional;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Core\Bootstrap;

class DatahandlerTest extends FunctionalTestCase
{

    /**
     * @var DataHandler
     */
    protected $dataHandler = null;

    /**
     * @var BackendUserAuthentication
     */
    protected $backendUser = null;

    /**
     * @var array Have styleguide loaded
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content.xml');
        Bootstrap::initializeLanguageObject();
        $this->backendUser = $this->setUpBackendUserFromFixture(1);
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        return $queryBuilder;
    }

    /**
     * @test
     */
    public function moveElementResolvesContainerId(): void
    {
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => '3-202',
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $this->dataHandler->start($datamap, [], $this->backendUser);
        $this->dataHandler->process_datamap();

        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertIsArray($row);
        $this->assertSame(3, (int)$row['tx_container_parent']);
        $this->assertSame(202, (int)$row['colPos']);
    }

    /**
     * @test
     */
    public function copyElementResolvesContainerId()
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 1,
                        'update' => [
                            'colPos' => '3-202',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                 $queryBuilder->expr()->eq(
                     't3_origuid',
                     $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                 )
            )
            ->execute()
            ->fetch();
        $this->assertIsArray($row);
        $this->assertSame(3, (int)$row['tx_container_parent']);
        $this->assertSame(202, (int)$row['colPos']);
    }

    /**
     * @test
     */
    public function localizeContainerWithContentKeepsColPosAndParentInNonConnectedMode(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copyToLanguage' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertIsArray($row);
        $this->assertSame(1, (int)$row['tx_container_parent']);
        $this->assertSame(200, (int)$row['colPos']);
    }

    /**
     * @test
     */
    public function localizeContainerWithContentKeepsColPosAndParentInConnectedMode(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'localize' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertIsArray($row);
        $this->assertSame(1, (int)$row['tx_container_parent']);
        $this->assertSame(200, (int)$row['colPos']);
    }

    /**
     * @test
     */
    public function deleteContainerDeleteChilds(): void
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
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertSame(1, $row['deleted']);
    }
}
