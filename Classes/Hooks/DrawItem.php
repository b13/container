<?php

declare(strict_types=1);

namespace B13\Container\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class DrawItem implements PageLayoutViewDrawItemHookInterface, SingletonInterface
{
    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(Registry $tcaRegistry)
    {
        $this->tcaRegistry = $tcaRegistry;
    }

    /**
     * @param PageLayoutView $parentObject : The parent object that triggered this hook
     * @param bool $drawItem : A switch to tell the parent object, if the item still must be drawn
     * @param string $headerContent : The content of the item header
     * @param string $itemContent : The content of the item itself
     * @param array $row : The current data row for this item
     */
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        $cType = $row['CType'];
        if ($this->tcaRegistry->isContainerElement($cType)) {
            $gridTemplate = $this->tcaRegistry->getGridTemplate($cType);
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->setTemplatePathAndFilename($gridTemplate);
            $view->assign('grid', $this->tcaRegistry->getGrid($cType));
            $view->assign('uid', $row['uid']);
            $row['tx_container_grid'] = $view->render();
        }
    }
}
