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

class LanguageStrictTest extends AbstractFrontendTest
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/LanguageStrict/setup.xml');
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
     */
    public function nothingTranslated(): void
    {
        $response = $this->executeFrontendRequest(new InternalRequest('/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-de</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     */
    public function bothTranslated(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/LanguageStrict/tt_content_both_translated.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/de'));
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
     */
    public function bothTranslatedTranslatedChildHidden(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/LanguageStrict/tt_content_both_translated_tranlated_child_hidden.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-de</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     */
    public function childTranslated(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/LanguageStrict/tt_content_child_translated.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-de</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    /**
     * @test
     */
    public function containerTranslated(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Frontend/Fixtures/LanguageStrict/tt_content_container_translated.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-default</h6>', $body);
        self::assertStringNotContainsString('<h6 class="header-children">header-de</h6>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }
}
