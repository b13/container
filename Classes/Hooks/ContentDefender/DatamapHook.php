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

class DatamapHook
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
    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        if (is_array($dataHandler->datamap['tt_content'])) {
            foreach ($dataHandler->datamap['tt_content'] as $id => $values) {
                if (
                    isset($values['tx_container_parent']) &&
                    $values['tx_container_parent'] > 0 &&
                    isset($values['colPos']) &&
                    $values['colPos'] > 0 &&
                    MathUtility::canBeInterpretedAsInteger($id)
                ) {
                    $this->datahandlerStorage->addMapping((int)$id, (int)$values['tx_container_parent']);
                }
            }
        }
    }
}
