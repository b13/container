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

class DefaultLanguageTest extends AbstractFrontendTest
{

    /**
     * @test
     * @group frontend
     */
    public function childrenAreRendered(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/default_language.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup.typoscript'],
            ]
        );
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-default</h6>', $body);
        self::assertStringContainsString('<h6 class="left-children">left-side-default</h6>', $body);
        // rendered content
        self::assertStringContainsString('<h2 class="">header-default</h2>', $body);
        self::assertStringContainsString('<h2 class="">left-side-default</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function childrenAreNotRenderedIfSkipOptionIsSet(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/default_language.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup_skip_rendering_child_content.typoscript'],
            ]
        );
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-default</h6>', $body);
        self::assertStringContainsString('<h6 class="left-children">left-side-default</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">left-side-default</h2>', $body);
    }
}
