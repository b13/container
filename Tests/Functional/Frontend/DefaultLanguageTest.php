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
        $response = $this->executeFrontendRequest(new InternalRequest());
        $body = (string)$response->getBody();
        $body = $this->prepareContent($body);
        $this->assertStringContainsString('<h1 class="container">container-default</h1>', $body);
        $this->assertStringContainsString('<h6 class="header-children">header-default</h6>', $body);
        $this->assertStringContainsString('<h6 class="left-children">left-side-default</h6>', $body);
        // rendered content
        $this->assertStringContainsString('<h2 class="">header-default</h2>', $body);
        $this->assertStringContainsString('<h2 class="">left-side-default</h2>', $body);
    }
}
