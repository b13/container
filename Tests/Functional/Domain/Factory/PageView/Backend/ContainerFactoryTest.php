<?php

namespace B13\Container\Tests\Functional\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ContainerFactoryTest extends FunctionalTestCase
{
    protected $typo3MajorVersion;

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @var non-empty-string[]
     */
    protected array $coreExtensionsToLoad = ['workspaces'];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $this->typo3MajorVersion = $typo3Version->getMajorVersion();
    }

    protected function getWorkspaceIdField(): string
    {
        if ($this->typo3MajorVersion < 11) {
            return '_ORIG_uid';
        }
        return 'uid';
    }

    /**
     * @test
     */
    public function localizedContainerChildElementsHasSortingOfDefaultChildElements(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/localizedContainerChildElementsHasSortingOfDefaultChildElements.csv');
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
    public function movedElementIntoOtherContainerInWorkspace(): void
    {
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/movedElementIntoOtherContainerInWorkspace.csv');
        } else {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/movedElementIntoOtherContainerInWorkspace.csv');
        }

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
        self::assertSame(104, $first[$this->getWorkspaceIdField()]);
    }

    /**
     * @test
     */
    public function movedElementIntoContainerInWorkspace(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/movedElementIntoContainerInWorkspace.csv');
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(101);
        $children = $container->getChildrenByColPos(200);
        self::assertSame(1, count($children));
        $first = $children[0];
        self::assertSame(200, $first['colPos']);
        self::assertSame(101, $first['tx_container_parent']);
    }

    /**
     * @test
     */
    public function containerRespectSortingOfMovedChildrenInWorkspace(): void
    {
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/movedChildrenInWorkspaceSorting.csv');
        } else {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/movedChildrenInWorkspaceSorting.csv');
        }
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(101);
        $children = $container->getChildrenByColPos(200);
        self::assertSame(2, count($children));
        $first = $children[0];
        self::assertSame(104, $first[$this->getWorkspaceIdField()]);
        $second = $children[1];
        self::assertSame(102, $second['uid']);
    }

    /**
     * @test
     */
    public function containerHoldsMovedChildrenInWorkspaceWithTranslation(): void
    {
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/movedChildrenInWorkspaceWithTranslation.csv');
        } else {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/movedChildrenInWorkspaceWithTranslation.csv');
        }
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
        self::assertSame(110, $first[$this->getWorkspaceIdField()]);
    }

    /**
     * @test
     */
    public function containerHoldsCopiedChildrenInWorkspace(): void
    {
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/copiedChildrenInWorkspace.csv');
        } else {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/copiedChildrenInWorkspace.csv');
        }
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
        self::assertSame(104, $first['uid']);
        if ($this->typo3MajorVersion < 11) {
            self::assertSame(105, $first['_ORIG_uid']);
        }
    }

    /**
     * @test
     */
    public function containerHoldsChildrenWhenMovedToOtherPage(): void
    {
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/container_moved_to_other_page.csv');
        } else {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/container_moved_to_other_page.csv');
        }
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        if ($this->typo3MajorVersion < 11) {
            // default record uid
            $container = $containerFactory->buildContainer(200);
        } else {
            // versioned record uid
            $container = $containerFactory->buildContainer(203);
        }
        $children = $container->getChildrenByColPos(201);
        self::assertSame(1, count($children));
        $first = $children[0];
        self::assertSame(205, $first[$this->getWorkspaceIdField()]);
    }

    /**
     * @test
     */
    public function containerHoldsLocalizedChildrenWhenMovedToOtherPage(): void
    {
        if ($this->typo3MajorVersion < 11) {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/container_moved_to_other_page.csv');
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/10/localized_container_moved_to_other_page.csv');
        } else {
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/container_moved_to_other_page.csv');
            $this->importCSVDataSet(__DIR__ . '/Fixture/ContainerFactory/localized_container_moved_to_other_page.csv');
        }
        $workspaceAspect = GeneralUtility::makeInstance(WorkspaceAspect::class, 1);
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', $workspaceAspect);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        if ($this->typo3MajorVersion < 11) {
            // default record uid
            $container = $containerFactory->buildContainer(210);
        } else {
            // versioned record uid
            $container = $containerFactory->buildContainer(213);
        }
        $children = $container->getChildrenByColPos(201);
        self::assertSame(1, count($children));
        $first = $children[0];
        self::assertSame(215, $first[$this->getWorkspaceIdField()]);
    }
}
