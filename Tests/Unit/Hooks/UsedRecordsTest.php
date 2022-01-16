<?php

declare(strict_types=1);
namespace B13\Container\Tests\Unit\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Domain\Model\Container;
use B13\Container\Hooks\UsedRecords;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UsedRecordsTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;
    /**
     * @test
     */
    public function addContainerChildrenReturnsUsedOfParamsIfTxContainerParentIsZero(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal());
        $params = [
            'used' => true,
            'record' => ['tx_container_parent' => 0]
        ];
        self::assertTrue($usedRecords->addContainerChildren($params, $pageLayoutView->reveal()));
        $params['used'] = false;
        self::assertFalse($usedRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsTrueIfChildrenInContainerColPos(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = $this->prophesize(Container::class);
        $container->getCType()->willReturn('myCType');
        $container->hasChildInColPos(2, 3)->willReturn(true);
        $containerFactory->buildContainer(1)->willReturn($container->reveal());
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvailableColumns('myCType')->willReturn([['colPos' => 2]]);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2, 'uid' => 3, 'sys_language_uid' => 0]
        ];
        self::assertTrue($usedRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfChildrenIsNotInContainerColPos(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = $this->prophesize(Container::class);
        $container->getCType()->willReturn('myCType');
        $container->hasChildInColPos(2, 3)->willReturn(false);
        $containerFactory->buildContainer(1)->willReturn($container->reveal());
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvailableColumns('myCType')->willReturn([['colPos' => 2]]);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2, 'uid' => 3, 'sys_language_uid' => 0]
        ];
        self::assertFalse($usedRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfChildrenIsNotInRegisterdGrid(): void
    {
        $pageLayoutView = $this->prophesize(PageLayoutView::class);
        $containerFactory = $this->prophesize(ContainerFactory::class);
        $container = $this->prophesize(Container::class);
        $container->getCType()->willReturn('myCType');
        $containerFactory->buildContainer(1)->willReturn($container->reveal());
        $tcaRegistry = $this->prophesize(Registry::class);
        $tcaRegistry->getAvailableColumns('myCType')->willReturn([['colPos' => 3]]);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory->reveal(), $tcaRegistry->reveal());
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2, 'uid' => 3]
        ];
        self::assertFalse($usedRecords->addContainerChildren($params, $pageLayoutView->reveal()));
    }
}
