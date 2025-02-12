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


use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerPreviewRenderer extends StandardContentPreviewRenderer
{
    protected GridRenderer $gridRenderer;
    protected FrontendInterface $runtimeCache;

    public function __construct(GridRenderer $gridRenderer, FrontendInterface $runtimeCache) {
        $this->gridRenderer = $gridRenderer;
        $this->runtimeCache = $runtimeCache;
    }

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        $this->runtimeCache->set('tx_container_current_gridColumItem', $item);
        return parent::renderPageModulePreviewHeader($item);
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 11) {
            return parent::renderPageModulePreviewContent($item);
        }
        $record = $item->getRecord();
        $record['tx_container_grid'] = $this->gridRenderer->renderGrid($record, $item->getContext());
        $item->setRecord($record);
        return parent::renderPageModulePreviewContent($item);
    }
}
