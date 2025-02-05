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

class ContainerPreviewRenderer extends StandardContentPreviewRenderer
{
    protected GridRenderer $gridRenderer;

    public function __construct(GridRenderer $gridRenderer) {
        $this->gridRenderer = $gridRenderer;
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();
        $record['tx_container_grid'] = $this->gridRenderer->renderGrid($record, $item->getContext(), $item);
        $item->setRecord($record);
        return parent::renderPageModulePreviewContent($item);
    }
}
