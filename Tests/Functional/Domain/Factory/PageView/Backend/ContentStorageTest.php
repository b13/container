<?php

namespace B13\Container\Tests\Functional\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use B13\Container\Domain\Factory\PageView\Backend\ContentStorage;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ContentStorageTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    protected array $coreExtensionsToLoad = ['workspaces'];

    #[Test]
    public function getContainerChildrenReturnsAllLiveChildrenInDraftWorkspace(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixture/ContentStorage/localizedContainerChildElementsHasSortingOfDefaultChildElements.csv');

        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        $database = $this->get(Database::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('workspace', $workspaceAspect);
        $contentStorage = GeneralUtility::makeInstance(ContentStorage::class, $database, $context);
        $containerRecord = ['uid' => 1, 'pid' => 1];
        $children = $contentStorage->getContainerChildren($containerRecord, 0);
        self::assertSame(2, count($children));
    }

    #[Test]
    public function getContainerChildrenReturnsAllLiveChildrenInLiveWorkspace(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixture/ContentStorage/localizedContainerChildElementsHasSortingOfDefaultChildElements.csv');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 0);
        $database = $this->get(Database::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('workspace', $workspaceAspect);
        $contentStorage = GeneralUtility::makeInstance(ContentStorage::class, $database, $context);
        $containerRecord = ['uid' => 1, 'pid' => 1];
        $children = $contentStorage->getContainerChildren($containerRecord, 0);
        self::assertSame(2, count($children));
    }

    #[Test]
    public function deletedChildInWorkspaceReturnsChildInLiveWorkspace(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixture/ContentStorage/deletedChildInWorkspace.csv');

        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 0);
        $database = $this->get(Database::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('workspace', $workspaceAspect);
        $contentStorage = GeneralUtility::makeInstance(ContentStorage::class, $database, $context);
        $containerRecord = ['uid' => 1, 'pid' => 1];
        $children = $contentStorage->getContainerChildren($containerRecord, 0);
        self::assertSame(1, count($children));
    }

    #[Test]
    public function deletedChildInWorkspaceReturnsNoChildInDraftWorkspace(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixture/ContentStorage/deletedChildInWorkspace.csv');

        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        $database = $this->get(Database::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('workspace', $workspaceAspect);
        $contentStorage = GeneralUtility::makeInstance(ContentStorage::class, $database, $context);
        $containerRecord = ['uid' => 1, 'pid' => 1];
        $children = $contentStorage->getContainerChildren($containerRecord, 0);
        self::assertSame(0, count($children));
    }
}
