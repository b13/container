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
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class WorkspaceTest extends AbstractFrontendTest
{
    protected $typo3MajorVersion;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $this->typo3MajorVersion = $typo3Version->getMajorVersion();
    }

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
        if ($this->typo3MajorVersion === 11) {
            self::markTestSkipped('todo WorkspacePreview Notice error is triggered');
        }
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
        if ($this->typo3MajorVersion === 11) {
            self::markTestSkipped('todo WorkspacePreview Notice error is triggered');
        }
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
        if ($this->typo3MajorVersion === 11) {
            self::markTestSkipped('todo WorkspacePreview Notice error is triggered');
        }
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/other_page.xml');
        if ($this->typo3MajorVersion < 11) {
            $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/10/container_moved_to_other_page.xml');
        } else {
            $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_moved_to_other_page.xml');
        }
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest(), $context);
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
        if ($this->typo3MajorVersion === 11) {
            self::markTestSkipped('todo WorkspacePreview Notice error is triggered');
        }
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_in_ws_whith_hidden_live_version.xml');
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest(), $context);
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
        if ($this->typo3MajorVersion === 11) {
            self::markTestSkipped('todo seems bug in core #93445');
        }
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/other_page.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/localized_pages.xml');
        if ($this->typo3MajorVersion < 11) {
            $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/10/container_moved_to_other_page.xml');
            $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/10/localized_container_moved_to_other_page.xml');
        } else {
            $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/container_moved_to_other_page.xml');
            $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/localized_container_moved_to_other_page.xml');
        }
        $context = (new InternalRequestContext())->withWorkspaceId(1)->withBackendUserId(1);
        $response = $this->executeFrontendRequest(new InternalRequest('http://localhost/de/'), $context);
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h2 class="">header-ws loc</h2>', $body);
    }
}
