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
        $container = new Container(['uid' => 2, 't3ver_oid' => 1, 'CType' => 'b13-container'], []);
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
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAfterChild($pageLayoutContext, $container, 111, 112);
        self::assertStringContainsString('tx_container_parent=1', $newContentUrl, 'should container uid of live workspace record');
    }

    #[Test]
    public function getNewContentUrlAtTopOfColumnContainsUidOfLiveWorkspaceAsContainerParent(): void
    {
        $container = new Container(['uid' => 2, 't3ver_oid' => 1, 'CType' => 'b13-container'], []);
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
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAtTopOfColumn($pageLayoutContext, $container, 111);
        self::assertStringContainsString('tx_container_parent=1', $newContentUrl, 'should container uid of live workspace record');
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
        $newContentUrl = $newContentUriBuilder->getNewContentUrlAtTopOfColumn($pageLayoutContext, $container, 111);
        self::assertNull($newContentUrl);
    }

    #[Test]
    public function getNewContentUrlAtTopOfColumnWithSingleAllowedCTypeContainsCreationOptionsDefaultValues(): void
    {
        $GLOBALS['TCA']['tt_content']['types']['tx_container_test']['creationOptions'] = [
            'defaultValues' => [
                'header' => 'Example header',
                'header_layout' => 5,
                'CType' => 'text',
                'colPos' => 999,
            ],
        ];
        $newContentUrl = urldecode($this->getNewContentUrlAtTopOfColumnWithSingleAllowedCType('tx_container_test'));
        self::assertStringContainsString('/record/edit?', $newContentUrl);
        self::assertStringContainsString('edit[tt_content][-5]=new', $newContentUrl);
        self::assertStringContainsString('defVals[tt_content][header]=Example header', $newContentUrl);
        self::assertStringContainsString('defVals[tt_content][header_layout]=5', $newContentUrl);
        self::assertStringContainsString('defVals[tt_content][CType]=tx_container_test', $newContentUrl, 'creationOptions must not override CType');
        self::assertStringContainsString('defVals[tt_content][colPos]=111', $newContentUrl, 'creationOptions must not override colPos');
        self::assertStringContainsString('defVals[tt_content][tx_container_parent]=1', $newContentUrl);
    }

    #[Test]
    public function getNewContentUrlAtTopOfColumnWithSingleAllowedCTypeAndSaveAndCloseCreatesRecordDirectly(): void
    {
        $GLOBALS['TCA']['tt_content']['types']['tx_container_test']['creationOptions'] = [
            'defaultValues' => [
                'header' => 'Example header',
            ],
            'saveAndClose' => true,
        ];
        $newContentUrl = urldecode($this->getNewContentUrlAtTopOfColumnWithSingleAllowedCType('tx_container_test'));
        self::assertStringContainsString('/record/commit?', $newContentUrl);
        self::assertMatchesRegularExpression('/data\[tt_content\]\[NEW[^\]]+\]\[header\]=Example header/', $newContentUrl);
        self::assertMatchesRegularExpression('/data\[tt_content\]\[NEW[^\]]+\]\[CType\]=tx_container_test/', $newContentUrl);
        self::assertMatchesRegularExpression('/data\[tt_content\]\[NEW[^\]]+\]\[colPos\]=111/', $newContentUrl);
        self::assertMatchesRegularExpression('/data\[tt_content\]\[NEW[^\]]+\]\[tx_container_parent\]=1/', $newContentUrl);
        self::assertMatchesRegularExpression('/data\[tt_content\]\[NEW[^\]]+\]\[pid\]=-5/', $newContentUrl);
    }

    protected function getNewContentUrlAtTopOfColumnWithSingleAllowedCType(string $allowedCType): string
    {
        $container = new Container(['uid' => 2, 't3ver_oid' => 1, 'CType' => 'b13-container'], []);
        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tcaRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllowedCTypesInColumn'])
            ->getMock();
        $tcaRegistry->expects(self::any())->method('getAllowedCTypesInColumn')->with('b13-container', 111)->willReturn([$allowedCType]);
        $containerColumnConfigurationService = $this->getMockBuilder(ContainerColumnConfigurationService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMaxitemsReached'])
            ->getMock();
        $containerColumnConfigurationService->expects(self::once())->method('isMaxitemsReached')->willReturn(false);
        $containerService  = $this->getMockBuilder(ContainerService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getNewContentElementAtTopTargetInColumn'])
            ->getMock();
        $containerService->expects(self::once())->method('getNewContentElementAtTopTargetInColumn')->willReturn(-5);
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $newContentUriBuilder = new NewContentUrlBuilder($tcaRegistry, $containerColumnConfigurationService, $containerService, $uriBuilder);
        return (string)$newContentUriBuilder->getNewContentUrlAtTopOfColumn($pageLayoutContext, $container, 111);
    }
}
