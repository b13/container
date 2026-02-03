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

use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerPreviewRenderer implements PreviewRendererInterface
{
    protected GridRenderer $gridRenderer;
    protected FrontendInterface $runtimeCache;
    protected StandardContentPreviewRenderer $standardContentPreviewRenderer;

    public function __construct(
        GridRenderer $gridRenderer,
        FrontendInterface $runtimeCache,
        StandardContentPreviewRenderer $standardContentPreviewRenderer
    ) {
        $this->gridRenderer = $gridRenderer;
        $this->runtimeCache = $runtimeCache;
        $this->standardContentPreviewRenderer = $standardContentPreviewRenderer;
    }

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        $this->runtimeCache->set('tx_container_current_gridColumItem', $item);
        return $this->standardContentPreviewRenderer->renderPageModulePreviewHeader($item);
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 11) {
            return $this->standardContentPreviewRenderer->renderPageModulePreviewContent($item);
        }
        $record = $item->getRecord();
        $record['tx_container_grid'] = $this->gridRenderer->renderGrid($record, $item->getContext());
        $item->setRecord($record);
        return $this->standardContentPreviewRenderer->renderPageModulePreviewContent($item);
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        return $this->standardContentPreviewRenderer->renderPageModulePreviewFooter($item);
    }

    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        return $this->standardContentPreviewRenderer->wrapPageModulePreview($previewHeader, $previewContent, $item);
    }
}
