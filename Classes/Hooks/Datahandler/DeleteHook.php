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
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[Autoconfigure(public: true)]
class DeleteHook
{
    public function __construct(protected ContainerFactory $containerFactory)
    {
    }

    public function processCmdmap_deleteAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted, DataHandler $dataHandler): void
    {
        if ($table === 'tt_content' && !$this->isAlreadyDeleted($table, $recordToDelete)) {
            $this->deleteChildren($id, $dataHandler->BE_USER, $dataHandler->enableLogging);
        }
    }

    /**
     * A record which is already soft-deleted had its children deleted when it was soft-deleted.
     * When such a record is purged (e.g. by cleanup:deletedrecords), the children must not be
     * resolved again: for a translated container in connected mode, they would be resolved via
     * the default language container and hit the children of another, active translation.
     *
     * @see https://github.com/b13/container/issues/695
     */
    protected function isAlreadyDeleted(string $table, array $recordToDelete): bool
    {
        $deleteField = $GLOBALS['TCA'][$table]['ctrl']['delete'] ?? '';
        return $deleteField !== '' && (int)($recordToDelete[$deleteField] ?? 0) === 1;
    }

    public function processCmdmap_discardAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted): void
    {
        if ($table === 'tt_content') {
            $this->deleteChildren($id, null);
        }
    }

    protected function deleteChildren(int $id, ?BackendUserAuthentication $backendUser, ?bool $enableLogging = null): void
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
                if ($enableLogging !== null) {
                    $localDataHandler->enableLogging = $enableLogging;
                }
                $localDataHandler->start([], $cmd, $backendUser);
                $localDataHandler->process_cmdmap();
            }
        } catch (Exception $e) {
            // nothing todo
        }
    }
}
