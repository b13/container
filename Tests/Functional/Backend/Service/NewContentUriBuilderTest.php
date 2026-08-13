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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class NewContentUriBuilderTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
    ];

    #[Test]
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
        self::assertStringEndsWith('#element-tt_content-112', $this->getReturnUrlParameter($newContentUrl));
    }

    #[Test]
    public function getNewContentEditUrlAfterChildContainsReturnAnchor(): void
    {
        $container = new Container(['uid' => 2], []);
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerColumnConfigurationService = $this->getMockBuilder(ContainerColumnConfigurationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $containerService = $this->getMockBuilder(ContainerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $newContentUriBuilder = new NewContentUrlBuilder($tcaRegistry, $containerColumnConfigurationService, $containerService, $uriBuilder);

        $newContentUrl = $newContentUriBuilder->getNewContentUrlAfterChild($pageLayoutContext, $container, 111, 112, ['CType' => 'header']);

        self::assertStringEndsWith('#element-tt_content-112', $this->getReturnUrlParameter($newContentUrl));
    }

    #[Test]
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
            ->onlyMethods(['getNewContentElementAtTopTargetInColumn'])
            ->getMock();
        $containerService->expects(self::once())->method('getNewContentElementAtTopTargetInColumn')->willReturn(-2);
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $newContentUriBuilder = new NewContentUrlBuilder($tcaRegistry, $containerColumnConfigurationService, $containerService, $uriBuilder);
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAtTopOfColumn($pageLayoutContext, $container, 111, null);
        self::assertStringContainsString('tx_container_parent=1', $newContentUrl, 'should container uid of live workspace record');
        self::assertStringEndsWith('#element-tt_content-2', $this->getReturnUrlParameter($newContentUrl));
    }

    #[Test]
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

    private function getReturnUrlParameter(string $url): string
    {
        parse_str((string)parse_url($url, PHP_URL_QUERY), $queryParameters);
        self::assertArrayHasKey('returnUrl', $queryParameters);
        self::assertIsString($queryParameters['returnUrl']);
        return $queryParameters['returnUrl'];
    }
}
