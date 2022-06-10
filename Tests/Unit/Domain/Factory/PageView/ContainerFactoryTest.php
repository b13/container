<?php

declare(strict_types=1);
namespace B13\Container\Tests\Unit\Domain\Factory\PageView;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use B13\Container\Domain\Factory\PageView\ContainerFactory;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ContainerFactoryTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function containerByUidReturnsNullIfNoRecordInDatabaseIsFound(): void
    {
        $database = $this->prophesize(Database::class);
        $database->fetchOneRecord(1)->willReturn(null);
        $tcaRegistry = $this->prophesize(Registry::class);
        $context = $this->prophesize(Context::class);
        $containerFactory = $this->getAccessibleMock(
            ContainerFactory::class,
            ['foo'],
            ['database' => $database->reveal(), 'tcaRegistry' => $tcaRegistry->reveal(), 'context' => $context->reveal()]
        );
        self::assertNull($containerFactory->_call('containerByUid', 1));
    }
}
