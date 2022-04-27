<?php

namespace B13\Container\Tests\Functional\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\PageView\Backend\ContentStorage;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ContentStorageTest extends FunctionalTestCase
{

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['workspaces'];

    /**
     * @test
     */
    public function foo(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/localizedContainerChildElementsHasSortingOfDefaultChildElements.xml');

        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $contentStorage = GeneralUtility::makeInstance(ContentStorage::class);
        $containerRecord = ['uid' => 1, 'pid' => 1];
        $children = $contentStorage->getContainerChildren($containerRecord, 0);
        self::assertSame(2, count($children));
    }

    /**
     * @test
     */
    public function bar(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/localizedContainerChildElementsHasSortingOfDefaultChildElements.xml');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 0);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $contentStorage = GeneralUtility::makeInstance(ContentStorage::class);
        $containerRecord = ['uid' => 1, 'pid' => 1];
        $children = $contentStorage->getContainerChildren($containerRecord, 0);
        self::assertSame(2, count($children));
    }
}
