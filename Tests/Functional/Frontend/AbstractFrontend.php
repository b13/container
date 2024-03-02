<?php

namespace B13\Container\Tests\Functional\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractFrontend extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected array $coreExtensionsToLoad = ['core', 'frontend', 'workspaces', 'fluid_styled_content'];

    /**
     * @var array<string, non-empty-string>
     */
    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/container/Build/sites' => 'typo3conf/sites',
    ];

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @param string $string
     * @return string
     */
    protected function prepareContent(string $string): string
    {
        $lines = explode("\n", $string);
        $notEmpty = [];
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                $notEmpty[] = trim($line);
            }
        }
        $content = implode('', $notEmpty);
        $content = preg_replace('/<div id="container-start"><\/div>(.*)<div id="container-end"><\/div>/', '$1', $content);
        return $content;
    }

    protected function executeFrontendRequestWrapper(InternalRequest $request, InternalRequestContext $context = null, bool $followRedirects = false): ResponseInterface
    {
        return $this->executeFrontendSubRequest($request, $context, $followRedirects);
    }
}
