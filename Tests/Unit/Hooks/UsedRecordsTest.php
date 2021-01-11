<?php

namespace B13\Container\Tests\Unit\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Model\Container;
use B13\Container\Hooks\UsedRecords;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class UsedRecordsTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;
    /**
     * @test
     */
    public function addContainerChildrenReturnsUsedOfParamsIfTxContainerParentIsZero()
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $tcaRegistry = $this->prophesize(Registry::class);
        $userRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => true,
            'record' => ['tx_container_parent' => 0]
        ];
        self::assertTrue($userRecords->addContainerChildren($params, $pageLayoutView->reveal()));
        $params['used'] = false;
        self::assertFalse($userRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsTrueIfColPosIsInConfiguredGrid()
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = new Container(['CType' => 'myCType'], []);
        $containerFactory->buildContainer(1)->willReturn($container);
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvailableColumns('myCType')->willReturn([['colPos' => 2]]);
        $userRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2]
        ];
        self::assertTrue($userRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfColPosIsNotInConfiguredGrid()
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = new Container(['CType' => 'myCType'], []);
        $containerFactory->buildContainer(1)->willReturn($container);
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvailableColumns('myCType')->willReturn([['colPos' => 3]]);
        $userRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => true,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2]
        ];
        self::assertFalse($userRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }
}
