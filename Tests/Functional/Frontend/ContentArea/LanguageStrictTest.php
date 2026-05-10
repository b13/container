<?php

namespace B13\Container\Tests\Functional\Frontend\ContentArea;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Frontend\AbstractFrontend;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

class LanguageStrictTest extends AbstractFrontend
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/LanguageStrict/setup.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => [
                    'EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup.typoscript',
                    'EXT:container_example/Configuration/Sets/ContainerExampleContentArea/setup.typoscript',
                ],
            ]
        );
    }

    #[Test]
    #[Group('frontend')]
    #[Group('v14-only')]
    public function nothingTranslated(): void
    {
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-de</h1>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    #[Group('v14-only')]
    public function bothTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/LanguageStrict/tt_content_both_translated.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        // rendered content
        self::assertStringContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    #[Group('v14-only')]
    public function bothTranslatedTranslatedChildHidden(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/LanguageStrict/tt_content_both_translated_tranlated_child_hidden.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    #[Group('v14-only')]
    public function childTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/LanguageStrict/tt_content_child_translated.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-de</h1>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }

    #[Test]
    #[Group('frontend')]
    #[Group('v14-only')]
    public function containerTranslated(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/LanguageStrict/tt_content_container_translated.csv');
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('<h1 class="container">container-de</h1>', $body);
        self::assertStringNotContainsString('<h1 class="container">container-default</h1>', $body);
        // rendered content
        self::assertStringNotContainsString('<h2 class="">header-de</h2>', $body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
    }
}
