<?php

namespace B13\Container\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

class DefaultLanguageTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = ['core', 'frontend', 'workspaces', 'fluid_styled_content'];

    /**
     * @var string[]
     */
    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/container/Build/sites' => 'typo3conf/sites',
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
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
     */
    public function childsAreRendered(): void
    {
        $response = $this->executeFrontendRequest(new InternalRequest());
        $body = (string)$response->getBody();
        $this->assertStringContainsString('<h1 class="container">container-default</h1>', $body, 'container-default heading not found');
        $this->assertStringContainsString('<h6 class="header-childs">header-default</h6>', $body, 'header-default heading not found');
        $this->assertStringContainsString('<h6 class="left-childs">left-side-default</h6>', $body, 'left-side-default heading not found');
    }
}
