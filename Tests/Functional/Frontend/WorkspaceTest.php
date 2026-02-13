<?php

namespace B13\Container\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;

class WorkspaceTest extends AbstractFrontend
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/setup.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup.typoscript'],
            ]
        );
    }

    #[Test]
    #[Group('frontend')]
    public function childInLiveIsRendered(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/container_with_ws_child.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest());
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-live</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-ws</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    public function childInWorkspaceIsRendered(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/container_with_ws_child.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-live</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    public function childInWorkspaceIsRenderedIfMovedFromOutsideContainer(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/container_with_ws_child_moved_from_outside.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('><h2>header (200)</h2><div class="header-children"><h6 class="header-children">header-ws</h6><div id="c201" class="frame frame-default frame-type-header frame-layout-0"><header><h2 class="">header-ws</h2></header>', $body);
        self::assertStringNotContainsString('<h2 class="">header-live</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    public function childInWorkspaceIsRenderendIfContainerIsMovedToOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/other_page.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/container_moved_to_other_page.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    public function containerInWorkspaceIsRenderedWhenLiveVersionIsHidden(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/container_in_ws_whith_hidden_live_version.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('ws-container-header', $body);
        self::assertStringContainsString('live-child-header', $body);
        self::assertStringNotContainsString('live-container-header', $body);
    }

    #[Test]
    #[Group('frontend')]
    public function childInWorkspaceIsRenderedWhenLiveVersionIsHidden(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/child_in_ws_whith_hidden_live_version.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('live-container-header', $body);
        self::assertStringContainsString('ws-child-header', $body);
        self::assertStringNotContainsString('live-child-header', $body);
    }

    #[Test]
    #[Group('frontend')]
    public function localizedChildInWorkspaceIsRenderendIfContainerWithLocalizationIsMovedToOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/other_page.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/localized_pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/container_moved_to_other_page.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Workspace/localized_container_moved_to_other_page.csv');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de/'), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws loc</h2>', $body);
    }
}
