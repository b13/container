<?php

declare(strict_types=1);

namespace B13\Container\Tests\Unit\Domain\Service;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Service\ConfigurationService;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ConfigurationServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function getAllAvailableColumnsReturnsEmptyArrayIfNoContainerConfigured(): void
    {
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->disableOriginalConstructor()->getMock();
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class, $eventDispatcher);
        $columns = $configurationService->getAllAvailableColumns();
        self::assertSame([], $columns);
    }
}
