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
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class DataProcessorWithFilesTest extends AbstractFrontendTest
{
    protected function setUp(): void
    {
        FunctionalTestCase::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/data_processor_with_files.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Fixtures/TypoScript/constants.typoscript'],
                'setup' => [
                    'EXT:container/Tests/Functional/Fixtures/TypoScript/setup.typoscript',
                    'EXT:container_example/Configuration/TypoScript/2cols.typoscript'
                ]
            ]
        );
    }

    /**
     * @test
     * @group frontend
     */
    public function relationIsRendered(): void
    {
        $response = $this->executeFrontendRequest(new InternalRequest('/'));
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        self::assertStringContainsString('README.md', $body);
    }
}
