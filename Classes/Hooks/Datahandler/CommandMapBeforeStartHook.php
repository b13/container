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
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\DataHandling\DataHandler;

#[Autoconfigure(public: true)]
class CommandMapBeforeStartHook
{
    public function __construct(
        protected ContainerFactory $containerFactory,
        protected Registry $tcaRegistry,
        protected Database $database,
        protected ContainerService $containerService
    ) {
    }

    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        $this->unsetInconsistentLocalizeCommands($dataHandler);
        $dataHandler->cmdmap = $this->rewriteSimpleCommandMap($dataHandler->cmdmap);
        $this->unsetInconsistentCopyOrMoveCommands($dataHandler);
        // previously page id is used for copy/moving element at top of a container colum
        // but this leeds to wrong sorting in page context (e.g. List-Module)
        $dataHandler->cmdmap = $this->rewriteCommandMapTargetForTopAtContainer($dataHandler->cmdmap);
        $dataHandler->cmdmap = $this->rewriteCommandMapTargetForAfterContainer($dataHandler->cmdmap);
    }

    protected function unsetInconsistentCopyOrMoveCommands(DataHandler $dataHandler): void
    {
        // a container should not be copied/moved inside himself
        if (!empty($dataHandler->cmdmap['tt_content'])) {
            foreach ($dataHandler->cmdmap['tt_content'] as $id => $cmds) {
                foreach ($cmds as $operation => $value) {
                    if (in_array($operation, ['copy', 'move'], true) === false) {
                        continue;
                    }

                    // move/copy element on top of container column
                    // proof target element has not element as container (nested)
                    if (isset($value['update']['tx_container_parent'])) {
                        $targetContainerId = (int)$value['update']['tx_container_parent'];
                        while ($targetContainerId > 0) {
                            if ($targetContainerId === $id) {
                                $this->logAndUnsetCmd($id, $operation, 'failed: container cannot be moved/copied into itself', $dataHandler);
                                break;
                            }
                            $record = $this->database->fetchOneRecord($targetContainerId);
                            $targetContainerId = (int)($record['tx_container_parent'] ?? 0);
                        }
                    }

                    // move/copy element after other element in container
                    // proof target element has not element as container (nested)
                    if ((is_array($value) && $value['target'] < 0) || (int)$value < 0) {
                        if (is_array($value)) {
                            $target = -(int)$value['target'];
                        } else {
                            // simple command
                            $target = -(int)$value;
                        }
                        $record = $this->database->fetchOneRecord($target);
                        while (isset($record['tx_container_parent']) && (int)$record['tx_container_parent'] > 0) {
                            if ((int)$record['tx_container_parent'] === $id) {
                                $this->logAndUnsetCmd($id, $operation, 'failed: container cannot be moved/copied into itself', $dataHandler);
                                break;
                            }
                            $record = $this->database->fetchOneRecord((int)$record['tx_container_parent']);
                        }
                    }
                }
            }
        }
    }

    protected function rewriteCommandMapTargetForAfterContainer(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmd) {
                foreach ($cmd as $operation => $value) {
                    if (in_array($operation, ['copy', 'move'], true) === false) {
                        continue;
                    }
                    if ((is_array($value) && $value['target'] < 0) || (int)$value < 0) {
                        if (is_array($value)) {
                            $target = -(int)$value['target'];
                        } else {
                            // simple command
                            $target = -(int)$value;
                        }
                        if (isset($value['update']['tx_container_parent']) && $target === (int)$value['update']['tx_container_parent']) {
                            // elements in container have already correct target
                            continue;
                        }
                        $record = $this->database->fetchOneRecord($target);
                        if ($record === null) {
                            // should not happen
                            continue;
                        }
                        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
                            continue;
                        }
                        try {
                            $container = $this->containerFactory->buildContainer((int)$record['uid']);
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
                        $targetRecordForOperation = $this->database->fetchOneRecord((int)abs($target));
                        if ($targetRecordForOperation === null) {
                            continue;
                        }
                        if ((int)$targetRecordForOperation['tx_container_parent'] > 0) {
                            // record will be copied/moved into container
                            $cmd = [
                                $operation => [
                                    'action' => 'paste',
                                    'target' => $target,
                                    'update' => [
                                        'colPos' => $targetRecordForOperation['colPos'],
                                        'sys_language_uid' => $targetRecordForOperation['sys_language_uid'],
                                        'tx_container_parent' => $targetRecordForOperation['tx_container_parent'],

                                    ],
                                ],
                            ];
                        } elseif ($this->tcaRegistry->isContainerElement($targetRecordForOperation['CType'])) {
                            // record will be copied/moved after container
                            $cmd = [
                                $operation => [
                                    'action' => 'paste',
                                    'target' => $target,
                                    'update' => [
                                        'colPos' => (int)$targetRecordForOperation['colPos'],
                                        'sys_language_uid' => $targetRecordForOperation['sys_language_uid'],

                                    ],
                                ],
                            ];
                        }
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
                            $container = $this->database->fetchOneRecord((int)$record['tx_container_parent']);
                            if ($container === null) {
                                // should not happen
                                continue;
                            }
                            $translatedContainer = $this->database->fetchOneTranslatedRecordByLocalizationParent((int)$container['uid'], (int)$data);
                            if ($translatedContainer === null || (int)$translatedContainer['l18n_parent'] === 0) {
                                $this->logAndUnsetCmd($id, $cmd, 'Localization failed: container is in free mode or not translated', $dataHandler);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function logAndUnsetCmd(int $id, string $cmd, string $message, DataHandler $dataHandler): void
    {
        $dataHandler->log(
            'tt_content',
            $id,
            1,
            null,
            1,
            $cmd . ' ' . $message,
            null
        );
        unset($dataHandler->cmdmap['tt_content'][$id][$cmd]);
        if (!empty($dataHandler->cmdmap['tt_content'][$id])) {
            unset($dataHandler->cmdmap['tt_content'][$id]);
        }
    }
}
