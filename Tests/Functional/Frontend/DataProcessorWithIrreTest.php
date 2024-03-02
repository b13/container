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

class DataProcessorWithIrreTest extends AbstractFrontend
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/data_processor_with_irre.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => [
                    'EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup.typoscript',
                    'EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/data_processor_with_irre.typoscript',
                ],
            ]
        );
    }

    /**
     * @test
     * @group frontend
     */
    public function relationIsRendered(): void
    {
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('irre-title-default', $body);
    }

    /**
     * @test
     * @group frontend
     */
    public function translatedRelationIsRendered(): void
    {
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/de'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('irre-title-translated', $body);
    }
}
