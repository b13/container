<?php

namespace  B13\Container\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\ContainerLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DrawItem implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @param PageLayoutView $parentObject : The parent object that triggered this hook
     * @param boolean $drawItem : A switch to tell the parent object, if the item still must be drawn
     * @param string $headerContent : The content of the item header
     * @param string $itemContent : The content of the item itself
     * @param array $row : The current data row for this item
     *
     * @return void
     */
    public function preProcess(PageLayoutView &$pageLayoutView, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {

        if ($row['CType'] === 'b13-2cols-with-header-container') {


            #$containerLayouView = GeneralUtility::makeInstance(ContainerLayoutView::class);
            #$containerLayouView->setContainerRecord($row);
            #$content = $containerLayouView->getTable_tt_content($row['pid']);

            #$drawItem = false;
            #$itemContent = $content;
        }

    }
}
