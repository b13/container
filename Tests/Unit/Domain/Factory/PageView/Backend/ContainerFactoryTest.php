<?php

declare(strict_types=1);

namespace B13\Container\Tests\Unit\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Domain\Factory\PageView\Backend\ContentStorage;
use B13\Container\Tca\Registry;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ContainerFactoryTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function containerByUidReturnsNullIfNoRecordInDatabaseIsFound(): void
    {
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOneRecord'])
            ->getMock();
        $database->expects(self::once())->method('fetchOneRecord')->with(1)->willReturn(null);
        $tcaRegistry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $context = $this->getMockBuilder(Context::class)->getMock();
        $contentStorage = $this->getMockBuilder(ContentStorage::class)->disableOriginalConstructor()->getMock();
        $containerFactory = $this->getMockBuilder($this->buildAccessibleProxy(ContainerFactory::class))
            ->setConstructorArgs(['database' => $database, 'tcaRegistry' => $tcaRegistry, 'context' => $context, 'contentStorage' => $contentStorage])
            ->onlyMethods([])
            ->getMock();
        self::assertNull($containerFactory->_call('containerByUid', 1));
    }
}
