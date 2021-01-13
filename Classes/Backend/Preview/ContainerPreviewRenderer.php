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
use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Tca\Registry;
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

    public function __construct(Registry $tcaRegistry = null, ContainerFactory $containerFactory = null)
    {
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
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
        foreach ($containerGrid as $row => $cols) {
            $rowObject = GeneralUtility::makeInstance(GridRow::class, $context);
            foreach ($cols as $col) {
                $columnObject = GeneralUtility::makeInstance(ContainerGridColumn::class, $context, $col, $container);
                $rowObject->addColumn($columnObject);
                if (isset($col['colPos'])) {
                    $records = $container->getChildrenByColPos($col['colPos']);
                    foreach ($records as $contentRecord) {
                        $columnItem = GeneralUtility::makeInstance(ContainerGridColumnItem::class, $context, $columnObject, $contentRecord, $container);
                        $columnObject->addItem($columnItem);
                    }
                }
            }
            $grid->addRow($rowObject);
        }

        $gridTemplate = $this->tcaRegistry->getGridTemplate($record['CType']);
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setPartialRootPaths(['EXT:backend/Resources/Private/Partials/', 'EXT:container/Resources/Private/Partials/']);
        $view->setTemplatePathAndFilename($gridTemplate);

        $view->assign('hideRestrictedColumns', (bool)(BackendUtility::getPagesTSconfig($context->getPageId())['mod.']['web_layout.']['hideRestrictedCols'] ?? false));
        $view->assign('newContentTitle', $this->getLanguageService()->getLL('newContentElement'));
        $view->assign('newContentTitleShort', $this->getLanguageService()->getLL('content'));
        $view->assign('allowEditContent', $this->getBackendUser()->check('tables_modify', 'tt_content'));
        $view->assign('containerGrid', $grid);

        $rendered = $view->render();

        return $content . $rendered;
    }
}
