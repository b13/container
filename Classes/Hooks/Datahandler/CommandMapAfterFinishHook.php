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

use B13\Container\Domain\Factory\Database;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CommandMapAfterFinishHook
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
     * @param DataHandler $dataHandler
     */
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
                $childrenInCopiedLanguage = $this->database->fetchRecordsByParentAndLanguage($id, $copyToLanguage);
                $data = [
                    'tt_content' => []
                ];
                foreach ($childrenInCopiedLanguage as $child) {
                    $data['tt_content'][$child['uid']] = ['tx_container_parent' => $newId];
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
