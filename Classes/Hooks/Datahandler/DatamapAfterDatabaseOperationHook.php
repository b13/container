<?php

namespace  B13\Container\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class DatamapAfterDatabaseOperationHook
{

    /**
     * @param string $status
     * @param string $table
     * @param mixed $id
     * @param array $fieldArray
     * @param DataHandler $dataHandler
     * @return void
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, $id, array $fieldArray, DataHandler $dataHandler): void
    {
        // change tx_container_parent of placeholder if necessary
        if (
            $table === 'tt_content' &&
            $status === 'update' &&
            MathUtility::canBeInterpretedAsInteger($id) &&
            is_array($dataHandler->datamap['tt_content'])
        ) {
            $datamapForPlaceHolders = ['tt_content' => []];
            foreach ($dataHandler->datamap['tt_content'] as $origId => $data) {
                if (!empty($data['tx_container_parent']) && $data['tx_container_parent'] > 0) {
                    $workspaceVersion = BackendUtility::getWorkspaceVersionOfRecord($dataHandler->BE_USER->workspace, $table, $origId, 'uid,t3ver_oid');
                    if ((int)$workspaceVersion['uid'] === (int)$id && (int)$workspaceVersion['uid'] !== (int)$origId) {
                        $datamapForPlaceHolders['tt_content'][$origId] = ['tx_container_parent' => $data['tx_container_parent']];
                    }
                }
            }
            if (count($datamapForPlaceHolders['tt_content']) > 0) {
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->bypassWorkspaceRestrictions = true;
                $localDataHandler->start($datamapForPlaceHolders, [], $dataHandler->BE_USER);
                $localDataHandler->process_datamap();
            }
        }
    }


}
