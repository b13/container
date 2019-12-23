<?php
namespace B13\Container\Tests\Functional\Datahandler;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Core\Bootstrap;

abstract class DatahandlerTest extends FunctionalTestCase
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
     * @var array
     */
    protected $coreExtensionsToLoad = ['workspaces'];

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
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
     * @param string $field
     * @param int $id
     * @return array
     */
    protected function fetchOneRecord(string $field, int $id): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    $field,
                    $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        $this->assertIsArray($row);
        return $row;
    }
}
