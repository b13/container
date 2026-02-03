<?php

declare(strict_types=1);

namespace B13\Container\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Preview\GridRenderer;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Information\Typo3Version;

class LegacyPageContentPreviewRendering
{
    protected GridRenderer $gridRenderer;
    protected Registry $tcaRegistry;

    public function __construct(GridRenderer $gridRenderer, Registry $tcaRegistry)
    {
        $this->gridRenderer = $gridRenderer;
        $this->tcaRegistry = $tcaRegistry;
    }

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') {
            return;
        }
        if ((new Typo3Version())->getMajorVersion() > 13) {
            return;
        }

        $record = $event->getRecord();
        $recordType = $record['CType'];
        if (!$this->tcaRegistry->isContainerElement($recordType)) {
            return;
        }
        $record['tx_container_grid'] = $this->gridRenderer->renderGrid($record, $event->getPageLayoutContext());
        $event->setRecord($record);
    }
}
