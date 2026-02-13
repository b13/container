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

class SiteLanguageFreeTest extends AbstractFrontend
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SiteLanguageFree/setup.csv');
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
    public function containerTranslatedInFreeModeSiteConfiguration(): void
    {
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/en-free'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringNotContainsString('<h2 class="">header-default</h2>', $body);
        self::assertStringContainsString('<h2 class="">header-translated</h2>', $body);
    }
}
