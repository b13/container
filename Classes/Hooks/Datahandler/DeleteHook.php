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

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteHook
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    public function __construct(ContainerFactory $containerFactory)
    {
        $this->containerFactory = $containerFactory;
    }

    public function processCmdmap_deleteAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted, DataHandler $dataHandler): void
    {
        if ($table === 'tt_content') {
            $this->deleteChildren($id, $dataHandler->BE_USER);
        }
    }

    public function processCmdmap_discardAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted): void
    {
        if ($table === 'tt_content') {
            $this->deleteChildren($id, null);
        }
    }

    protected function deleteChildren(int $id, ?BackendUserAuthentication $backendUser): void
    {
        try {
            $container = $this->containerFactory->buildContainer($id);
            $children = $container->getChildRecords();
            $toDelete = [];
            foreach ($children as $colPos => $record) {
                $toDelete[$record['uid']] = ['delete' => 1];
            }
            if (!empty($toDelete)) {
                $cmd = ['tt_content' => $toDelete];
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->start([], $cmd, $backendUser);
                $localDataHandler->process_cmdmap();
            }
        } catch (Exception $e) {
            // nothing todo
        }
    }
}
