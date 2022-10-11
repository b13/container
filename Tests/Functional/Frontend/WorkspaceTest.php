<?php

namespace B13\Container\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;

class WorkspaceTest extends AbstractFrontendTest
{
    protected $typo3MajorVersion;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $this->typo3MajorVersion = $typo3Version->getMajorVersion();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/setup.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup.typoscript'],
            ]
        );
    }

    /**
     * @test
     * @group frontend
     */
    public function childInLiveIsRendered(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/container_with_ws_child.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest());
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-live</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-ws</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function childInWorkspaceIsRendered(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/container_with_ws_child.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-live</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function childInWorkspaceIsRenderedIfMovedFromOutsideContainer(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/container_with_ws_child_moved_from_outside.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('><h2>header (200)</h2><div class="header-children"><h6 class="header-children">header-ws</h6><div id="c201" class="frame frame-default frame-type-header frame-layout-0"><header><h2 class="">header-ws</h2></header>', $body);
        self::assertStringNotContainsString('<h2 class="">header-live</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function childInWorkspaceIsRenderendIfContainerIsMovedToOtherPage(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/other_page.csv');
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/10/container_moved_to_other_page.csv');
        } else {
            $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/container_moved_to_other_page.csv');
        }
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function containerInWorkspaceIsRenderedWhenLiveVersionIsHidden(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/container_in_ws_whith_hidden_live_version.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('ws-container-header', $body);
        self::assertStringContainsString('live-child-header', $body);
        self::assertStringNotContainsString('live-container-header', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function localizedChildInWorkspaceIsRenderendIfContainerWithLocalizationIsMovedToOtherPage(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/other_page.csv');
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/localized_pages.csv');
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/10/container_moved_to_other_page.csv');
            $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/10/localized_container_moved_to_other_page.csv');
        } else {
            $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/container_moved_to_other_page.csv');
            $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/Workspace/localized_container_moved_to_other_page.csv');
        }
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de/'), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws loc</h2>', $body);
    }
}
