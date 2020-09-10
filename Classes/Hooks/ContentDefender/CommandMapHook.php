<?php

declare(strict_types=1);

namespace B13\Container\Hooks\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class CommandMapHook
{
    /**
     * @var DatahandlerStorage
     */
    protected $datahandlerStorage;

    public function __construct(DatahandlerStorage $datahandlerStorage = null)
    {
        $this->datahandlerStorage = $datahandlerStorage ?? GeneralUtility::makeInstance(DatahandlerStorage::class);
    }

    /**
     * @param DataHandler $dataHandler
     */
    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        if (!empty($dataHandler->cmdmap['tt_content'])) {
            foreach ($dataHandler->cmdmap['tt_content'] as $id => $cmds) {
                foreach ($cmds as $cmd => $data) {
                    if (
                        ($cmd === 'copy' || $cmd === 'move') &&
                        (!empty($data['update'])) &&
                        isset($data['update']['colPos']) &&
                        $data['update']['colPos'] > 0 &&
                        isset($data['update']['tx_container_parent']) &&
                        $data['update']['tx_container_parent'] > 0 &&
                        MathUtility::canBeInterpretedAsInteger($id)
                    ) {
                        $this->datahandlerStorage->addMapping($id, (int)$data['update']['tx_container_parent']);
                    }
                }
            }
        }
    }
}
