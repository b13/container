<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

class DataProcessorWithDataTest extends AbstractFrontend
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/data_processor_with_data.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/constants.typoscript'],
                'setup' => [
                    'EXT:container/Tests/Functional/Frontend/Fixtures/TypoScript/setup.typoscript',
                    'EXT:container_example/Configuration/TypoScript/2cols.typoscript',
                ],
            ]
        );
    }

    /**
     * @test
     * @group frontend
     */
    public function modHeaderIsRendered(): void
    {
        $response = $this->executeFrontendRequestWrapper(new InternalRequest('http://localhost/'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('my-mod-header:header', $body);
    }
}
