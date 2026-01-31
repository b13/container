<?php

declare(strict_types=1);

namespace B13\Container\Hooks\Datahandler\ContentElementRestriction;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Hooks\Datahandler\DatahandlerProcess;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\View\BackendLayoutView;
use TYPO3\CMS\Core\DataHandling\DataHandler;

#[Autoconfigure(public: true)]
readonly class DataHandlerHook extends \TYPO3\CMS\Backend\Hooks\DataHandlerContentElementRestrictionHook
{
    public function __construct(BackendLayoutView $backendLayoutView, private readonly DatahandlerProcess $datahandlerProcess)
    {
        parent::__construct($backendLayoutView);
    }

    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        $cmdmap = $dataHandler->cmdmap;
        if (empty($cmdmap['tt_content']) || $dataHandler->bypassAccessCheckForRecords) {
            return;
        }
        foreach ($cmdmap['tt_content'] as $id => $incomingFieldArray) {
            if ($this->datahandlerProcess->isContainerInProcess($id)) {
            }
        }
    }

    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        $datamap = $dataHandler->datamap;
        if (empty($datamap['tt_content']) || $dataHandler->bypassAccessCheckForRecords) {
            return;
        }
        foreach ($datamap['tt_content'] as $id => $incomingFieldArray) {
        }
    }
}
