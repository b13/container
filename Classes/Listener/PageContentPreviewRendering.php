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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageContentPreviewRendering
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

        $record = $event->getRecord();

        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 13) {
            $recordType = $record->getRecordType();
        } else {
            $recordType = $record['CType'];
        }
        if (!$this->tcaRegistry->isContainerElement($recordType)) {
            return;
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 13) {
            $previewContent = $this->gridRenderer->renderGrid($record->toArray(), $event->getPageLayoutContext());
            $event->setPreviewContent($previewContent);
        } else {
            $record['tx_container_grid'] = $this->gridRenderer->renderGrid($record, $event->getPageLayoutContext());
            $event->setRecord($record);
        }
    }
}
