<?php

declare(strict_types=1);

namespace B13\Container\Hooks\Datahandler;

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

class DatamapAfterDatabaseOperationHook
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @param Database|null $database
     */
    public function __construct(Database $database = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
    }

    /**
     * @param string $status
     * @param string $table
     * @param mixed $id
     * @param array $fieldArray
     * @param DataHandler $dataHandler
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, $id, array $fieldArray, DataHandler $dataHandler): void
    {
        // change tx_container_parent of placeholder if necessary
        if (
            $table === 'tt_content' &&
            $status === 'update' &&
            $dataHandler->BE_USER->workspace > 0 &&
            MathUtility::canBeInterpretedAsInteger($id) &&
            is_array($dataHandler->datamap['tt_content'])
        ) {
            $datamapForPlaceHolders = ['tt_content' => []];
            foreach ($dataHandler->datamap['tt_content'] as $origId => $data) {
                if (!empty($data['tx_container_parent']) && $data['tx_container_parent'] > 0) {
                    $origRecord = $this->database->fetchOneRecord((int)$origId);
                    // origRecord is copied placeholder
                    if (
                        (int)$origRecord['t3ver_oid'] === 0 &&
                        (int)$origRecord['tx_container_parent'] !== (int)$data['tx_container_parent'] &&
                        (int)$origRecord['t3ver_wsid'] === (int)$dataHandler->BE_USER->workspace
                    ) {
                        $datamapForPlaceHolders['tt_content'][$origId] = ['tx_container_parent' => $data['tx_container_parent']];
                    } else {
                        // origRecord is moved placehoder
                        $origRecord = $this->database->fetchOneMovedRecord((int)$origId);
                        if (
                            (int)$origRecord['t3ver_oid'] === 0 &&
                            (int)$origRecord['tx_container_parent'] !== (int)$data['tx_container_parent'] &&
                            (int)$origRecord['t3ver_wsid'] === (int)$dataHandler->BE_USER->workspace
                        ) {
                            $datamapForPlaceHolders['tt_content'][$origRecord['uid']] = ['tx_container_parent' => $data['tx_container_parent']];
                        }
                    }
                }
            }
            if (!empty($datamapForPlaceHolders['tt_content'])) {
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->bypassWorkspaceRestrictions = true;
                $localDataHandler->start($datamapForPlaceHolders, [], $dataHandler->BE_USER);
                $localDataHandler->process_datamap();
            }
        }
    }
}
