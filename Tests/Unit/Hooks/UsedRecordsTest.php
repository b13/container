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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UsedRecordsTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function addContainerChildrenReturnsUsedOfParamsIfTxContainerParentIsZero(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            self::markTestSkipped('>= v12 is tested by Listener ContentUsedOnPageTest');
        }
        $pageLayoutView = $this->getMockBuilder(PageLayoutView::class)->disableOriginalConstructor()->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->disableOriginalConstructor()->getMock();
        $registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory, $registry);
        $params = [
            'used' => true,
            'record' => ['tx_container_parent' => 0],
        ];
        self::assertTrue($usedRecords->addContainerChildren($params, $pageLayoutView));
        $params['used'] = false;
        self::assertFalse($usedRecords->addContainerChildren($params, $pageLayoutView));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsTrueIfChildrenInContainerColPos(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            self::markTestSkipped('>= v12 is tested by Listener ContentUsedOnPageTest');
        }
        $pageLayoutView = $this->getMockBuilder(PageLayoutView::class)->disableOriginalConstructor()->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildContainer'])
            ->getMock();
        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCType', 'hasChildInColPos'])
            ->getMock();
        $container->expects(self::once())->method('getCType')->willReturn('myCType');
        $container->expects(self::once())->method('hasChildInColPos')->with(2, 3)->willReturn(true);
        $containerFactory->expects(self::once())->method('buildContainer')->with(1)->willReturn($container);
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->onlyMethods(['getAvailableColumns'])->getMock();
        $tcaRegistry->expects(self::once())->method('getAvailableColumns')->with('myCType')->willReturn([['colPos' => 2]]);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory, $tcaRegistry);
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2, 'uid' => 3, 'sys_language_uid' => 0],
        ];
        self::assertTrue($usedRecords->addContainerChildren($params, $pageLayoutView));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfChildrenIsNotInContainerColPos(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            self::markTestSkipped('>= v12 is tested by Listener ContentUsedOnPageTest');
        }
        $pageLayoutView = $this->getMockBuilder(PageLayoutView::class)->disableOriginalConstructor()->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildContainer'])
            ->getMock();
        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCType', 'hasChildInColPos'])
            ->getMock();
        $container->expects(self::once())->method('getCType')->willReturn('myCType');
        $container->expects(self::once())->method('hasChildInColPos')->with(2, 3)->willReturn(false);
        $containerFactory->expects(self::once())->method('buildContainer')->with(1)->willReturn($container);
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->onlyMethods(['getAvailableColumns'])->getMock();
        $tcaRegistry->expects(self::once())->method('getAvailableColumns')->with('myCType')->willReturn([['colPos' => 2]]);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory, $tcaRegistry);
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2, 'uid' => 3, 'sys_language_uid' => 0],
        ];
        self::assertFalse($usedRecords->addContainerChildren($params, $pageLayoutView));
    }

    /**
     * @test
     */
    public function addContainerChildrenReturnsFalseIfChildrenIsNotInRegisterdGrid(): void
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            self::markTestSkipped('>= v12 is tested by Listener ContentUsedOnPageTest');
        }
        $pageLayoutView = $this->getMockBuilder(PageLayoutView::class)->disableOriginalConstructor()->getMock();
        $containerFactory = $this->getMockBuilder(ContainerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildContainer'])
            ->getMock();
        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCType'])
            ->getMock();
        $container->expects(self::once())->method('getCType')->willReturn('myCType');
        $containerFactory->expects(self::once())->method('buildContainer')->with(1)->willReturn($container);
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->onlyMethods(['getAvailableColumns'])->getMock();
        $tcaRegistry->expects(self::once())->method('getAvailableColumns')->with('myCType')->willReturn([['colPos' => 3]]);
        $usedRecords = GeneralUtility::makeInstance(UsedRecords::class, $containerFactory, $tcaRegistry);
        $params = [
            'used' => false,
            'record' => ['tx_container_parent' => 1, 'colPos' => 2, 'uid' => 3],
        ];
        self::assertFalse($usedRecords->addContainerChildren($params, $pageLayoutView));
    }
}
