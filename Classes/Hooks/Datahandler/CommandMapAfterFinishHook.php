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

class CommandMapAfterFinishHook
{
    /**
     * @var Database
     */
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function processCmdmap_afterFinish(DataHandler $dataHandler): void
    {
        $cmdmap = $dataHandler->cmdmap;
        $copyMappingArray_merged = $dataHandler->copyMappingArray_merged;

        foreach ($cmdmap as $table => $incomingCmdArrayPerId) {
            if ($table !== 'tt_content') {
                continue;
            }
            foreach ($incomingCmdArrayPerId as $id => $incomingCmdArray) {
                if (!is_array($incomingCmdArray)) {
                    continue;
                }
                if (empty($incomingCmdArray['copyToLanguage'])) {
                    continue;
                }
                if (empty($copyMappingArray_merged['tt_content'][$id])) {
                    continue;
                }
                $copyToLanguage = $incomingCmdArray['copyToLanguage'];
                $newId = $copyMappingArray_merged['tt_content'][$id];
                $data = [
                    'tt_content' => [],
                ];
                // child in free mode is copied
                $child = $this->database->fetchOneRecord($newId);
                if ($child === null) {
                    continue;
                }
                if ($child['tx_container_parent'] > 0) {
                    $copiedFromChild = $this->database->fetchOneRecord($id);
                    // copied from non default language (connectecd mode) children
                    if ($copiedFromChild !== null && $copiedFromChild['sys_language_uid'] > 0 && $copiedFromChild['l18n_parent'] > 0) {
                        // fetch orig container
                        $origContainer = $this->database->fetchOneTranslatedRecordByLocalizationParent((int)$copiedFromChild['tx_container_parent'], $copiedFromChild['sys_language_uid']);
                        // should never be null
                        if ($origContainer !== null) {
                            $freeModeContainer = $this->database->fetchContainerRecordLocalizedFreeMode((int)$origContainer['uid'], $copyToLanguage);
                            if ($freeModeContainer !== null) {
                                $data['tt_content'][$newId] = ['tx_container_parent' => (int)$freeModeContainer['uid']];
                            }
                        }
                    } else {
                        $freeModeContainer = $this->database->fetchContainerRecordLocalizedFreeMode((int)$child['tx_container_parent'], $copyToLanguage);
                        if ($freeModeContainer !== null) {
                            $data['tt_content'][$newId] = ['tx_container_parent' => (int)$freeModeContainer['uid']];
                        }
                    }
                }
                if (empty($data['tt_content'])) {
                    continue;
                }
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->start($data, [], $dataHandler->BE_USER);
                $localDataHandler->process_datamap();
            }
        }
    }
}
