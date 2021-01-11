<?php

namespace B13\Container\Tests\Unit\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class UsedRecordsTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function getAllAvailableColumnsReturnsEmptyArrayIfNoContainerConfigured()
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $columns = $registry->getAllAvailableColumns();
        self::assertSame([], $columns);
    }

    /**
     * @test
     */
    public function addPageTSReturnsOriginalTSIfNoContainerConfigured()
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $res = $registry->addPageTS(['foo'], 1, [], []);
        self::assertSame([['foo'], 1, [], []], $res);
    }
}
