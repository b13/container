<?php

namespace B13\Container\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class WorkspaceTest extends AbstractFrontendTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        FunctionalTestCase::setUp();

        $this->importDataSet('PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_users.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/root_page.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Fixtures/TypoScript/constants.typoscript'],
                'setup' => ['EXT:container/Tests/Functional/Fixtures/TypoScript/setup.typoscript']
            ]
        );
    }

    /**
     * @test
     * @group frontend
     */
    public function childInLiveIsRendered(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_with_ws_child.xml');
        $response = $this->executeFrontendRequest(new InternalRequest());
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
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_with_ws_child.xml');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest(), $context);
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
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_with_ws_child_moved_from_outside.xml');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest(), $context);
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
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/other_page.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_moved_to_other_page.xml');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest(), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        var_dump($body);
        self::assertStringContainsString('<h2 class="">header-ws</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function localizedChildInWorkspaceIsRenderendIfContainerWithLocalizationIsMovedToOtherPage(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/other_page.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/localized_pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_moved_to_other_page.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/localized_container_moved_to_other_page.xml');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest('http://localhost/de/'), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        var_dump($body);
        self::assertStringContainsString('<h2 class="">header-ws loc</h2>', $body);
    }
}
