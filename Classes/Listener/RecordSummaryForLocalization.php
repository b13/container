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

use B13\Container\Service\RecordLocalizeSummaryModifier;
use TYPO3\CMS\Backend\Controller\Event\AfterRecordSummaryForLocalizationEvent;

class RecordSummaryForLocalization
{
    /**
     * @var RecordLocalizeSummaryModifier
     */
    protected $recordLocalizeSummaryModifier;

    public function __construct(RecordLocalizeSummaryModifier $recordLocalizeSummaryModifier)
    {
        $this->recordLocalizeSummaryModifier = $recordLocalizeSummaryModifier;
    }

    public function __invoke(AfterRecordSummaryForLocalizationEvent $event): void
    {
        $records = $event->getRecords();
        $columns = $event->getColumns();
        $records = $this->recordLocalizeSummaryModifier->filterRecords($records);
        $columns = $this->recordLocalizeSummaryModifier->rebuildColumns($columns);
        $event->setColumns($columns);
        $event->setRecords($records);
    }
}
