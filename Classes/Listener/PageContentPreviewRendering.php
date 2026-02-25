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
use B13\Container\Domain\Core\RecordWithRenderedGrid;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Information\Typo3Version;

#[AsEventListener(identifier: 'tx-container-page-content-preview-rendering', before: 'typo3-backend/fluid-preview/content')]
class PageContentPreviewRendering
{
    public function __construct(protected GridRenderer $gridRenderer, protected Registry $tcaRegistry)
    {
    }

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') {
            return;
        }
        if ((new Typo3Version())->getMajorVersion() < 14) {
            return;
        }

        $record = $event->getRecord();
        $recordType = $record->getRecordType();
        if (!$this->tcaRegistry->isContainerElement($recordType)) {
            return;
        }
        if ($record instanceof Record) {
            $previewContent = $this->gridRenderer->renderGrid($record->toArray(), $event->getPageLayoutContext());
            $recordWithRenderedGrid = new RecordWithRenderedGrid($record, $previewContent);
            $event->setRecord($recordWithRenderedGrid);
        }
    }
}
