<?php

declare(strict_types=1);

namespace B13\Container\ContentDefender\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\ContentDefender\ContainerColumnConfigurationService;
use B13\Container\Hooks\Datahandler\DatahandlerProcess;
use IchHabRecht\ContentDefender\Hooks\CmdmapDataHandlerHook;
use IchHabRecht\ContentDefender\Repository\ContentRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class CommandMapHook extends CmdmapDataHandlerHook
{
    /**
     * @var ContainerColumnConfigurationService
     */
    protected $containerColumnConfigurationService;

    protected $mapping = [];

    public function __construct(
        ContentRepository $contentRepository = null,
        ContainerColumnConfigurationService $containerColumnConfigurationService = null
    ) {
        $this->containerColumnConfigurationService = $containerColumnConfigurationService ?? GeneralUtility::makeInstance(ContainerColumnConfigurationService::class);
        parent::__construct($contentRepository);
    }

    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        if (!empty($dataHandler->cmdmap['tt_content'])) {
            foreach ($dataHandler->cmdmap['tt_content'] as $id => $cmds) {
                foreach ($cmds as $cmd => $data) {
                    if ($cmd === 'copy') {
                        $this->containerColumnConfigurationService->setContainerIsCopied($id);
                    }
                    if (
                        ($cmd === 'copy' || $cmd === 'move') &&
                        (!empty($data['update'])) &&
                        isset($data['update']['colPos']) &&
                        $data['update']['colPos'] > 0 &&
                        isset($data['update']['tx_container_parent']) &&
                        $data['update']['tx_container_parent'] > 0 &&
                        MathUtility::canBeInterpretedAsInteger($id)
                    ) {
                        $this->mapping[(int)$id] = [
                            'containerId' => (int)$data['update']['tx_container_parent'],
                            'colPos' => (int)$data['update']['colPos'],
                        ];
                        $this->containerColumnConfigurationService->addCopyMapping(
                            (int)$id,
                            (int)$data['update']['tx_container_parent'],
                            (int)$data['update']['colPos']
                        );
                        $useChildId = null;
                        if ($cmd === 'move') {
                            $useChildId = $id;
                        }

                        if ($this->containerColumnConfigurationService->isMaxitemsReachedByContainenrId((int)$data['update']['tx_container_parent'], (int)$data['update']['colPos'], $useChildId)) {
                            unset($dataHandler->cmdmap['tt_content'][$id]);
                            $dataHandler->log(
                                'tt_content',
                                $id,
                                1,
                                0,
                                1,
                                'The command couldn\'t be executed due to reached maxitems configuration',
                                28
                            );
                        }
                    }
                }
            }
        }
        parent::processCmdmap_beforeStart($dataHandler);
    }

    protected function isRecordAllowedByRestriction(array $columnConfiguration, array $record): bool
    {
        if (isset($record['tx_container_parent']) &&
            $record['tx_container_parent'] > 0 &&
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->isContainerInProcess((int)$record['tx_container_parent'])
        ) {
            return true;
        }
        if (isset($this->mapping[$record['uid']])) {
            $columnConfiguration = $this->containerColumnConfigurationService->override(
                $columnConfiguration,
                $this->mapping[$record['uid']]['containerId'],
                $this->mapping[$record['uid']]['colPos']
            );
        }
        return parent::isRecordAllowedByRestriction($columnConfiguration, $record);
    }

    protected function isRecordAllowedByItemsCount(array $columnConfiguration, array $record): bool
    {
        if (isset($record['tx_container_parent']) &&
            $record['tx_container_parent'] > 0 &&
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->isContainerInProcess((int)$record['tx_container_parent'])
        ) {
            return true;
        }
        if (isset($this->mapping[$record['uid']])) {
            return true;
        }
        return parent::isRecordAllowedByItemsCount($columnConfiguration, $record);
    }
}
