<?php

namespace B13\Container\Tests\Functional\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ContainerFactoryTest extends FunctionalTestCase
{

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['workspaces'];

    /**
     * @test
     */
    public function localizedContainerChildElementsHasSortingOfDefaultChildElements(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/localizedContainerChildElementsHasSortingOfDefaultChildElements.xml');
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(2);
        $children = $container->getChildrenByColPos(201);
        self::assertSame(2, count($children));
        $first = $children[0];
        self::assertSame(6, $first['uid']);
    }

    /**
     * @test
     */
    public function containerHoldsMovedChildrenInWorkspaceClipboard(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/movedChildrenInWorkspaceClipboard.xml');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(101);
        $children = $container->getChildrenByColPos(200);
        self::assertSame(0, count($children));
        $container = $containerFactory->buildContainer(103);
        $children = $container->getChildrenByColPos(201);
        self::assertSame(1, count($children));
        $first = $children[0];
        self::assertSame(104, $first['_ORIG_uid']);
    }

    /**
     * @test
     */
    public function containerRespectSortingOfMovedChildrenInWorkspace(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/movedChildrenInWorkspaceSorting.xml');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(101);
        $children = $container->getChildrenByColPos(200);
        self::assertSame(2, count($children));
        $first = $children[0];
        self::assertSame(104, $first['_ORIG_uid']);
        $second = $children[1];
        self::assertSame(102, $second['uid']);
    }

    /**
     * @test
     */
    public function containerHoldsMovedChildrenInWorkspaceWithTranslation(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/movedChildrenInWorkspaceWithTranslation.xml');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        GeneralUtility::makeInstance(Context::class)->setAspect('language', $languageAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(106);
        $children = $container->getChildrenByColPos(200);
        self::assertSame(0, count($children));
        $container = $containerFactory->buildContainer(104);
        $children = $container->getChildrenByColPos(202);
        self::assertSame(1, count($children));
        $first = $children[0];
        self::assertSame(110, $first['_ORIG_uid']);
    }

    /**
     * @test
     */
    public function containerHoldsCopiedChildrenInWorkspaceAjax(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/Workspace/sys_workspace.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/copiedChildrenInWorkspace.xml');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(101);
        $children = $container->getChildrenByColPos(200);
        self::assertSame(1, count($children));
        $container = $containerFactory->buildContainer(103);
        $children = $container->getChildrenByColPos(201);
        self::assertSame(1, count($children));
        $first = $children[0];
        self::assertSame(105, $first['_ORIG_uid']);
    }
}
