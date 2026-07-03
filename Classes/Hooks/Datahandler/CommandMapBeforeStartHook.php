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
use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Tca\Registry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $dataHandler->cmdmap = $this->setContainerIdToZeroIfNotSetOnUpdate($dataHandler->cmdmap);
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

    protected function setContainerIdToZeroIfNotSetOnUpdate(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmds) {
                foreach ($cmds as &$cmd) {
                    if (
                        (!empty($cmd['update'])) &&
                        isset($cmd['update']['colPos']) &&
                        !isset($cmd['update']['tx_container_parent'])
                    ) {
                        $cmd['update']['tx_container_parent'] = 0;
                    }
                }
            }
        }
        return $cmdmap;
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

                    // Path 1: drop-at-top rewritten from a positive page-uid target.
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
                            if ($target === -$container->getUid()) {
                                // Fallback anchor active. Ensure the invariant
                                // container.sorting < min(child.sorting) so DataHandler
                                // computes the new sorting within the children's sort space
                                // instead of around the container record itself (see #738).
                                $this->normalizeContainerChildrenSorting($container);
                            }
                            $cmd[$operation]['target'] = $target;
                        } catch (Exception $e) {
                            // not a container
                        }
                    }

                    // Path 2: command that already arrives with target = -container_uid.
                    // The Path 1 branch skips this case (target > 0 gate), so the
                    // -container_uid anchor reaches DataHandler unchanged. Ensure the
                    // invariant here as well (see #738).
                    if (
                        isset($value['update']) &&
                        isset($value['update']['tx_container_parent']) &&
                        $value['update']['tx_container_parent'] > 0 &&
                        isset($value['update']['colPos']) &&
                        $value['update']['colPos'] > 0 &&
                        isset($value['target']) &&
                        (int)$value['target'] < 0 &&
                        abs((int)$value['target']) === (int)$value['update']['tx_container_parent']
                    ) {
                        try {
                            $container = $this->containerFactory->buildContainer((int)$value['update']['tx_container_parent']);
                            $this->normalizeContainerChildrenSorting($container);
                        } catch (Exception $e) {
                            // not a container
                        }
                    }
                }
            }
        }
        return $cmdmap;
    }

    /**
     * Renumber the container's children so that all sorting values sit strictly
     * above the container's own sorting. Invoked from the drop-at-top path when
     * the anchor falls back to -container_uid. Without this, DataHandler would
     * compute the new sorting from the container record's own page-level
     * sorting, which is outside the children's sort range whenever
     * container.sorting > min(child.sorting) (see #738).
     *
     * Writes are workspace-aware: the current BE user's active workspace scopes
     * both the read and the update.
     */
    protected function normalizeContainerChildrenSorting(Container $container): void
    {
        $containerRecord = $container->getContainerRecord();
        $containerUid = (int)$containerRecord['uid'];
        $containerSorting = (int)$containerRecord['sorting'];
        $workspace = (int)($GLOBALS['BE_USER']->workspace ?? 0);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
        $qb = $connection->createQueryBuilder();
        $qb->getRestrictions()->removeAll();
        $qb->select('uid', 'sorting')
            ->from('tt_content')
            ->where(
                $qb->expr()->eq('tx_container_parent', $qb->createNamedParameter($containerUid, Connection::PARAM_INT)),
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0, Connection::PARAM_INT)),
                $qb->expr()->eq('t3ver_wsid', $qb->createNamedParameter($workspace, Connection::PARAM_INT)),
            )
            ->orderBy('sorting', 'ASC')
            ->addOrderBy('uid', 'ASC');
        $children = $qb->executeQuery()->fetchAllAssociative();

        if ($children === []) {
            return;
        }
        if ((int)$children[0]['sorting'] > $containerSorting) {
            // invariant already holds
            return;
        }

        $newSorting = $containerSorting + 256;
        $tstamp = time();
        foreach ($children as $child) {
            $childUid = (int)$child['uid'];
            if ((int)$child['sorting'] !== $newSorting) {
                $connection->update(
                    'tt_content',
                    ['sorting' => $newSorting, 'tstamp' => $tstamp],
                    ['uid' => $childUid],
                    ['sorting' => Connection::PARAM_INT, 'tstamp' => Connection::PARAM_INT, 'uid' => Connection::PARAM_INT]
                );
            }
            $newSorting += 256;
        }
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
                    if (empty($cmd[$operation])) {
                        continue;
                    }
                    if (is_array($cmd[$operation])) {
                        continue;
                    }
                    $target = (int)$cmd[$operation];
                    if ($target < 0) {
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
                        } else {
                            $cmd = [
                                $operation => [
                                    'action' => 'paste',
                                    'target' => $target,
                                    'update' => [],
                                ],
                            ];
                        }
                    } else {
                        $cmd = [
                            $operation => [
                                'action' => 'paste',
                                'target' => $target,
                                'update' => [],
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
