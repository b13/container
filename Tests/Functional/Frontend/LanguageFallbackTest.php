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

class LanguageFallbackTest extends AbstractFrontendTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LanguageFallback/setup.csv');
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
    public function nothingTranslated(): void
    {
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/fr'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-fr</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-default</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-fr</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-fr</h2>', $body);
        self::assertStringContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function bothTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LanguageFallback/tt_content_both_translated.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/fr'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-fr</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-fr</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        // rendered content
        self::assertStringContainsString('<h2 class="">header-fr</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function fallbackForStrictLanguageToOtherTranslationFreeMode(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LanguageFallback/tt_content_fallback_for_strict_language_to_other_translation_free_mode.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/ch'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-de</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        // rendered content
        self::assertStringContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function bothTranslatedTranslatedChildHidden(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LanguageFallback/tt_content_both_translated_tranlated_child_hidden.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/fr'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-fr</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-fr</h6>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-default</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-fr</h2>', $body);
        self::assertStringContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function childTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LanguageFallback/tt_content_child_translated.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/fr'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-fr</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-fr</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        // rendered content
        self::assertStringContainsString('<h2 class="">header-fr</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function containerTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LanguageFallback/tt_content_container_translated.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/fr'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-fr</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringContainsString('<h6 class="header-children">header-default</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-fr</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-fr</h2>', $body);
        self::assertStringContainsString('<h2 class="">header-default</h2>', $body);
    }
}
