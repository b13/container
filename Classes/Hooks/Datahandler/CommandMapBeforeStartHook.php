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
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class CommandMapBeforeStartHook
{
    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var ContainerService
     */
    protected $containerService;

    public function __construct(
        ContainerFactory $containerFactory,
        Registry $tcaRegistry,
        Database $database,
        ContainerService $containerService
    ) {
        $this->containerFactory = $containerFactory;
        $this->tcaRegistry = $tcaRegistry;
        $this->database = $database;
        $this->containerService = $containerService;
    }

    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        $this->unsetInconsistentLocalizeCommands($dataHandler);
        $dataHandler->cmdmap = $this->rewriteSimpleCommandMap($dataHandler->cmdmap);
        $dataHandler->cmdmap = $this->extractContainerIdFromColPosOnUpdate($dataHandler->cmdmap);
        // previously page id is used for copy/moving element at top of a container colum
        // but this leeds to wrong sorting in page context (e.g. List-Module)
        $dataHandler->cmdmap = $this->rewriteCommandMapTargetForTopAtContainer($dataHandler->cmdmap);
        $dataHandler->cmdmap = $this->rewriteCommandMapTargetForAfterContainer($dataHandler->cmdmap);
    }

    protected function rewriteCommandMapTargetForAfterContainer(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmd) {
                foreach ($cmd as $operation => $value) {
                    if (in_array($operation, ['copy', 'move'], true) === false) {
                        continue;
                    }
                    if (
                        (!isset($value['update']['tx_container_parent']) || (int)$value['update']['tx_container_parent'] === 0) &&
                        ((is_array($value) && $value['target'] < 0) || (int)$value < 0)
                    ) {
                        if (is_array($value)) {
                            $target = -(int)$value['target'];
                        } else {
                            // simple command
                            $target = -(int)$value;
                        }
                        $record = $this->database->fetchOneRecord($target);
                        if ($record === null || $record['tx_container_parent'] > 0) {
                            // elements in container have already correct target
                            continue;
                        }
                        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
                            continue;
                        }
                        try {
                            $container = $this->containerFactory->buildContainer($record['uid']);
                            $target = $this->containerService->getAfterContainerElementTarget($container);
                            if (is_array($value)) {
                                $cmd[$operation]['target'] = $target;
                            } else {
                                // simple command
                                $cmd[$operation] = $target;
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
        return $cmdmap;
    }

    protected function rewriteCommandMapTargetForTopAtContainer(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmd) {
                foreach ($cmd as $operation => $value) {
                    if (in_array($operation, ['copy', 'move'], true) === false) {
                        continue;
                    }

                    if (
                        isset($value['update']) &&
                        isset($value['update']['tx_container_parent']) &&
                        $value['update']['tx_container_parent'] > 0 &&
                        isset($value['update']['colPos']) &&
                        $value['update']['colPos'] > 0 &&
                        $value['target'] > 0
                    ) {
                        try {
                            $container = $this->containerFactory->buildContainer((int)$value['update']['tx_container_parent']);
                            $target = $this->containerService->getNewContentElementAtTopTargetInColumn($container, (int)$value['update']['colPos']);
                            $cmd[$operation]['target'] = $target;
                        } catch (Exception $e) {
                            // not a container
                        }
                    }
                }
            }
        }
        return $cmdmap;
    }

    protected function rewriteSimpleCommandMap(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmd) {
                if (empty($cmd['copy']) && empty($cmd['move'])) {
                    continue;
                }
                foreach ($cmd as $operation => $value) {
                    if (in_array($operation, ['copy', 'move'], true) === false) {
                        continue;
                    }
                    if (is_array($cmd[$operation])) {
                        continue;
                    }
                    if ((int)$cmd[$operation] < 0) {
                        $target = (int)$cmd[$operation];
                        $recordToCopy = $this->database->fetchOneRecord((int)abs($target));
                        if ($recordToCopy === null || $recordToCopy['tx_container_parent'] === 0) {
                            continue;
                        }
                        $cmd = [
                                $operation => [
                                    'action' => 'paste',
                                    'target' => $target,
                                    'update' => [
                                        'colPos' => $recordToCopy['tx_container_parent'] . '-' . $recordToCopy['colPos'],
                                        'sys_language_uid' => $recordToCopy['sys_language_uid'],

                                    ],
                                ],
                            ];
                    }
                }
            }
        }
        return $cmdmap;
    }

    protected function unsetInconsistentLocalizeCommands(DataHandler $dataHandler): void
    {
        if (!empty($dataHandler->cmdmap['tt_content'])) {
            foreach ($dataHandler->cmdmap['tt_content'] as $id => $cmds) {
                foreach ($cmds as $cmd => $data) {
                    if ($cmd === 'localize') {
                        $record = $this->database->fetchOneRecord((int)$id);
                        if ($record !== null && $record['tx_container_parent'] > 0) {
                            $container = $this->database->fetchOneRecord($record['tx_container_parent']);
                            if ($container === null) {
                                // should not happen
                                continue;
                            }
                            $translatedContainer = $this->database->fetchOneTranslatedRecordByLocalizationParent($container['uid'], (int)$data);
                            if ($translatedContainer === null || (int)$translatedContainer['l18n_parent'] === 0) {
                                $dataHandler->log(
                                    'tt_content',
                                    $id,
                                    1,
                                    0,
                                    1,
                                    'Localization failed: container is in free mode or not translated',
                                    28
                                );
                                unset($dataHandler->cmdmap['tt_content'][$id][$cmd]);
                                if (!empty($dataHandler->cmdmap['tt_content'][$id])) {
                                    unset($dataHandler->cmdmap['tt_content'][$id]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function extractContainerIdFromColPosOnUpdate(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmds) {
                foreach ($cmds as &$cmd) {
                    if (
                        (!empty($cmd['update'])) &&
                        isset($cmd['update']['colPos'])
                    ) {
                        $cmd['update'] = $this->dataFromContainerIdColPos($cmd['update']);
                    }
                }
            }
        }
        return $cmdmap;
    }

    protected function dataFromContainerIdColPos(array $data): array
    {
        $colPos = $data['colPos'];
        if (MathUtility::canBeInterpretedAsInteger($colPos) === false) {
            [$containerId, $newColPos] = GeneralUtility::intExplode('-', $colPos);
            $data['colPos'] = $newColPos;
            $data['tx_container_parent'] = $containerId;
        } elseif (!isset($data['tx_container_parent'])) {
            $data['tx_container_parent'] = 0;
            $data['colPos'] = (int)$colPos;
        }
        return $data;
    }
}
