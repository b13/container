<?php
namespace B13\Container\Tests\Unit\Hooks;

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Model\Container;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use B13\Container\Hooks\UsedRecords;

class UsedRecordsTest extends UnitTestCase
{

    protected $resetSingletonInstances = true;
    /**
     * @test
     */
    public function addContainerChildsReturnsUsedOfParamsIfTxContainerParentIsZero(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $tcaRegistry = $this->prophesize(Registry::class);
        $userRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => true,
            'record' => ['tx_container_parent' => 0]
        ];
        $this->assertTrue($userRecords->addContainerChilds($params, $pageLayoutView->reveal()));
        $params['used'] = false;
        $this->assertFalse($userRecords->addContainerChilds($params, $pageLayoutView->reveal()));
    }

    /**
     * @test
     */
    public function addContainerChildsReturnsTrueIfColPosIsInConfiguredGrid(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = new Container(['CType' => 'myCType'], []);
        $containerFactory->buildContainer(1)->willReturn($container);
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvaiableColumns('myCType')->willReturn([['colPos' => 2]]);
        $userRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2]
        ];
        $this->assertTrue($userRecords->addContainerChilds($params, $pageLayoutView->reveal()));

    }

    /**
     * @test
     */
    public function addContainerChildsReturnsFalseIfColPosIsNotInConfiguredGrid(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = new Container(['CType' => 'myCType'], []);
        $containerFactory->buildContainer(1)->willReturn($container);
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvaiableColumns('myCType')->willReturn([['colPos' => 3]]);
        $userRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => true,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2]
        ];
        $this->assertFalse($userRecords->addContainerChilds($params, $pageLayoutView->reveal()));
    }
}
