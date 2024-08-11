<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Backend\Service;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Service\NewContentUrlBuilder;
use B13\Container\ContentDefender\ContainerColumnConfigurationService;
use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class NewContentUriBuilderTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
    ];

    /**
     * @test
     */
    public function getNewContentUrlAfterChildContainsUidOfLiveWorkspaceAsContainerParent(): void
    {
        $container = new Container(['uid' => 2, 't3ver_oid' => 1], []);
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPageId'])
            ->getMock();
        $pageLayoutContext->expects(self::any())->method('getPageId')->willReturn(3);
        $tcaRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerColumnConfigurationService = $this->getMockBuilder(ContainerColumnConfigurationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerService  = $this->getMockBuilder(ContainerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $newContentUriBuilder = new NewContentUrlBuilder($tcaRegistry, $containerColumnConfigurationService, $containerService, $uriBuilder);
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAfterChild($pageLayoutContext, $container, 111, 112, null);
        self::assertStringContainsString('tx_container_parent=1', $newContentUrl, 'should container uid of live workspace record');
    }

    /**
     * @test
     */
    public function getNewContentUrlAtTopOfColumnContainsUidOfLiveWorkspaceAsContainerParent(): void
    {
        $container = new Container(['uid' => 2, 't3ver_oid' => 1], []);
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPageId'])
            ->getMock();
        $pageLayoutContext->expects(self::any())->method('getPageId')->willReturn(3);
        $tcaRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerColumnConfigurationService = $this->getMockBuilder(ContainerColumnConfigurationService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMaxitemsReached'])
            ->getMock();
        $containerColumnConfigurationService->expects(self::once())->method('isMaxitemsReached')->willReturn(false);
        $containerService  = $this->getMockBuilder(ContainerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $newContentUriBuilder = new NewContentUrlBuilder($tcaRegistry, $containerColumnConfigurationService, $containerService, $uriBuilder);
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAtTopOfColumn($pageLayoutContext, $container, 111, null);
        self::assertStringContainsString('tx_container_parent=1', $newContentUrl, 'should container uid of live workspace record');
    }

    /**
     * @test
     */
    public function getNewContentUrlAtTopOfColumnReturnsNullIfMaxitemsIsReached(): void
    {
        $container = new Container([], []);
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerColumnConfigurationService = $this->getMockBuilder(ContainerColumnConfigurationService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMaxitemsReached'])
            ->getMock();
        $containerColumnConfigurationService->expects(self::once())->method('isMaxitemsReached')->willReturn(true);
        $containerService  = $this->getMockBuilder(ContainerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uriBuilder  = $this->getMockBuilder(UriBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $newContentUriBuilder = new NewContentUrlBuilder($tcaRegistry, $containerColumnConfigurationService, $containerService, $uriBuilder);
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAtTopOfColumn($pageLayoutContext, $container, 111, null);
        self::assertNull($newContentUrl);
    }
}
