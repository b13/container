<?php

declare(strict_types=1);

namespace B13\Container\Backend\Preview;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Grid\ContainerGridColumn;
use B13\Container\Backend\Grid\ContainerGridColumnItem;
use B13\Container\Backend\Service\NewContentUrlBuilder;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Events\BeforeContainerPreviewIsRenderedEvent;
use B13\Container\Tca\Registry;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\Grid;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridRow;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ContainerPreviewRenderer extends StandardContentPreviewRenderer
{
    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    protected NewContentUrlBuilder $newContentUrlBuilder;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        Registry $tcaRegistry,
        ContainerFactory $containerFactory,
        NewContentUrlBuilder $newContentUrlBuilder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tcaRegistry = $tcaRegistry;
        $this->containerFactory = $containerFactory;
        $this->newContentUrlBuilder = $newContentUrlBuilder;
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $content = parent::renderPageModulePreviewContent($item);
        $context = $item->getContext();
        $record = $item->getRecord();
        $grid = GeneralUtility::makeInstance(Grid::class, $context);
        try {
            $container = $this->containerFactory->buildContainer((int)$record['uid']);
        } catch (Exception $e) {
            // not a container
            return $content;
        }
        $containerGrid = $this->tcaRegistry->getGrid($record['CType']);
        foreach ($containerGrid as $cols) {
            $rowObject = GeneralUtility::makeInstance(GridRow::class, $context);
            foreach ($cols as $col) {
                $defVals = $this->getDefValsForContentDefenderAllowsOnlyOneSpecificContentType($record['CType'], (int)$col['colPos']);
                $url = $this->newContentUrlBuilder->getNewContentUrlAtTopOfColumn($context, $container, (int)$col['colPos'], $defVals);
                $columnObject = GeneralUtility::makeInstance(ContainerGridColumn::class, $context, $col, $container, $url, $defVals !== null);
                $rowObject->addColumn($columnObject);
                if (isset($col['colPos'])) {
                    $records = $container->getChildrenByColPos($col['colPos']);
                    foreach ($records as $contentRecord) {
                        $url = $this->newContentUrlBuilder->getNewContentUrlAfterChild($context, $container, (int)$col['colPos'], (int)$contentRecord['uid'], $defVals);
                        $columnItem = GeneralUtility::makeInstance(ContainerGridColumnItem::class, $context, $columnObject, $contentRecord, $container, $url);
                        $columnObject->addItem($columnItem);
                    }
                }
            }
            $grid->addRow($rowObject);
        }

        $gridTemplate = $this->tcaRegistry->getGridTemplate($record['CType']);
        $partialRootPaths = $this->tcaRegistry->getGridPartialPaths($record['CType']);
        $layoutRootPaths = $this->tcaRegistry->getGridLayoutPaths($record['CType']);
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setPartialRootPaths($partialRootPaths);
        $view->setLayoutRootPaths($layoutRootPaths);
        $view->setTemplatePathAndFilename($gridTemplate);

        $view->assign('hideRestrictedColumns', (bool)(BackendUtility::getPagesTSconfig($context->getPageId())['mod.']['web_layout.']['hideRestrictedCols'] ?? false));
        $view->assign('newContentTitle', $this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_layout.xlf:newContentElement'));
        $view->assign('newContentTitleShort', $this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_layout.xlf:content'));
        $view->assign('allowEditContent', $this->getBackendUser()->check('tables_modify', 'tt_content'));
        // keep compatibility
        $view->assign('containerGrid', $grid);
        $view->assign('grid', $grid);
        $view->assign('containerRecord', $record);
        $beforeContainerPreviewIsRendered = new BeforeContainerPreviewIsRenderedEvent($container, $view);
        $this->eventDispatcher->dispatch($beforeContainerPreviewIsRendered);
        $rendered = $view->render();

        return $content . $rendered;
    }

    protected function getDefValsForContentDefenderAllowsOnlyOneSpecificContentType(string $cType, int $colPos): ?array
    {
        $contentDefefenderConfiguration = $this->tcaRegistry->getContentDefenderConfiguration($cType, $colPos);
        $allowedCTypes = GeneralUtility::trimExplode(',', $contentDefefenderConfiguration['allowed.']['CType'] ?? '', true);
        $allowedListTypes = GeneralUtility::trimExplode(',', $contentDefefenderConfiguration['allowed.']['list_type'] ?? '', true);
        if (count($allowedCTypes) === 1) {
            if ($allowedCTypes[0] !== 'list') {
                return ['CType' => $allowedCTypes[0]];
            }
            if (count($allowedListTypes) === 1) {
                return ['CType' => 'list', 'list_type' => $allowedListTypes[0]];
            }
        }
        return null;
    }
}
