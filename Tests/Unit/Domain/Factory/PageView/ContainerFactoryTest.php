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
        $containerFactory = $this->getAccessibleMock(
            ContainerFactory::class,
            ['foo'],
            ['database' => $database->reveal(), 'tcaRegistry' => null, 'context' => null]
        );
        self::assertNull($containerFactory->_call('containerByUid', 1));
    }
}
