<?php

declare(strict_types=1);

namespace B13\Container\Integrity;

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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Sorting
{
    /**
     * Default sort interval used when renumbering children. Matches
     * DataHandler::$sortIntervals (which is instance state, not a constant,
     * so we mirror it here).
     */
    public const SORT_INTERVAL = 256;

    protected array $errors = [];

    public function __construct(
        protected Database $database,
        protected Registry $tcaRegistry,
        protected ContainerFactory $containerFactory,
        protected ContainerService $containerService
    ) {
    }

    public function run(bool $dryRun = true, bool $enableLogging = false, ?int $pid = null): array
    {
        $cTypes = $this->tcaRegistry->getRegisteredCTypes();
        $containerRecords = $this->database->getContainerRecords($cTypes, $pid);
        $containerRecords = array_merge($containerRecords, $this->database->getContainerRecordsFreeMode($cTypes, $pid));
        $colPosByCType = [];
        foreach ($cTypes as $cType) {
            $columns = $this->tcaRegistry->getAvailableColumns($cType);
            $colPosByCType[$cType] = [];
            foreach ($columns as $column) {
                $colPosByCType[$cType][] = $column['colPos'];
            }
            $this->unsetContentDefenderConfiguration($cType);
        }
        $this->fixChildrenSorting($containerRecords, $colPosByCType, $dryRun, $enableLogging);
        return $this->errors;
    }

    protected function unsetContentDefenderConfiguration(string $cType): void
    {
        // unset content_defender configuration for migration because already unallowed children in container may exist
        foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'] ?? [] as $rowKey => $row) {
            foreach ($row as $colKey => $column) {
                $column['allowedContentTypes'] = '';
                $column['disallowedContentTypes'] = '';
                $column['allowed'] = [];
                $column['disallowed'] = [];
                $column['maxitems'] = 0;
                $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'][$rowKey][$colKey] = $column;
            }
        }
    }

    /**
     * Renumber the container's children so that all sorting values sit strictly
     * above the container's own sorting, preserving the configured container
     * column order (`tcaRegistry` order) and the within-column sorting order.
     *
     * Intended for the drop-at-top path in `CommandMapBeforeStartHook`: when
     * the `-container_uid` fallback anchor is used, the invariant
     * `container.sorting < min(child.sorting)` must hold, otherwise
     * DataHandler computes the moved record's new sorting from the container's
     * own page-level sorting instead of the children's sort space
     * (see #738).
     *
     * The read side reuses the `Container` model for language and configured
     * column-order scoping.
     *
     * Writes are direct `Connection::update()`s of `sorting` and `tstamp` —
     * intentionally not routed through DataHandler to avoid re-entering the
     * hook stack from within a hook. Only touches the container's own direct
     * children.
     *
     * Workspaces are intentionally out of scope for this initial fix: the
     * method short-circuits when the current BE user is not in workspace 0.
     * The reason is that in a non-live workspace `ContainerFactory` can
     * return live records for children that have no workspace overlay yet;
     * a direct SQL update would then silently mutate live rows from a
     * workspace DataHandler run. The workspace path deserves its own,
     * overlay-aware follow-up.
     */
    public function normalizeChildrenSortingForContainer(Container $container): void
    {
        $workspace = (int)($GLOBALS['BE_USER']->workspace ?? 0);
        if ($workspace !== 0) {
            // Non-live workspace: skip. See method docblock for rationale.
            return;
        }

        $containerRecord = $container->getContainerRecord();
        $containerSorting = (int)$containerRecord['sorting'];
        $cType = $container->getCType();

        $orderedChildren = [];
        foreach ($this->tcaRegistry->getAvailableColumns($cType) as $column) {
            $children = $container->getChildrenByColPos((int)$column['colPos']);
            foreach ($children as $child) {
                $orderedChildren[] = $child;
            }
        }

        if ($orderedChildren === []) {
            return;
        }

        $minChildSorting = null;
        foreach ($orderedChildren as $child) {
            $sorting = (int)$child['sorting'];
            if ($minChildSorting === null || $sorting < $minChildSorting) {
                $minChildSorting = $sorting;
            }
        }
        if ($minChildSorting !== null && $minChildSorting > $containerSorting) {
            return;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
        $newSorting = $containerSorting + self::SORT_INTERVAL;
        $tstamp = time();
        foreach ($orderedChildren as $child) {
            $childUid = (int)$child['uid'];
            if ((int)$child['sorting'] !== $newSorting) {
                $connection->update(
                    'tt_content',
                    ['sorting' => $newSorting, 'tstamp' => $tstamp],
                    ['uid' => $childUid],
                    ['sorting' => Connection::PARAM_INT, 'tstamp' => Connection::PARAM_INT, 'uid' => Connection::PARAM_INT]
                );
            }
            $newSorting += self::SORT_INTERVAL;
        }
    }

    protected function fixChildrenSortingUpdateRequired(Container $container, array $colPosByCType): bool
    {
        $containerRecord = $container->getContainerRecord();
        $prevSorting = $containerRecord['sorting'];
        foreach ($colPosByCType[$containerRecord['CType']] as $colPos) {
            $children = $container->getChildrenByColPos($colPos);
            foreach ($children as $child) {
                if ($child['sorting'] <= $prevSorting) {
                    $this->errors[] = '- pid ' . $containerRecord['pid'] . ', container uid ' . $containerRecord['uid'] . ' must be fixed';
                    return true;
                }
                $prevSorting = $child['sorting'];
                if ($this->tcaRegistry->isContainerElement($child['CType'])) {
                    $childContainer = $this->containerFactory->buildContainer((int)$child['uid']);
                    $targetUid = (-1) * $this->containerService->getAfterContainerElementTarget($childContainer);
                    if ($childContainer->getUid() !== $targetUid) {
                        $sorting = $this->database->getSortingByUid($targetUid);
                        if ($child['sorting'] <= $sorting) {
                            $prevSorting = $sorting;
                        }
                    }
                }
            }
        }
        return false;
    }

    protected function fixChildrenSorting(array $containerRecords, array $colPosByCType, bool $dryRun, bool $enableLogging): void
    {
        $datahandler = GeneralUtility::makeInstance(DataHandler::class);
        $datahandler->enableLogging = $enableLogging;
        foreach ($containerRecords as $containerRecord) {
            try {
                $container = $this->containerFactory->buildContainer((int)$containerRecord['uid']);
            } catch (Exception $e) {
                // should not happend
                continue;
            }
            if ($this->fixChildrenSortingUpdateRequired($container, $colPosByCType) === false || $dryRun === true) {
                continue;
            }
            $prevChild = null;
            foreach ($colPosByCType[$containerRecord['CType']] as $colPos) {
                $children = $container->getChildrenByColPos($colPos);
                if (empty($children)) {
                    continue;
                }
                foreach ($children as $child) {
                    if ($prevChild === null) {
                        $cmdmap = [
                            'tt_content' => [
                                $child['uid'] => [
                                    'move' => [
                                        'action' => 'paste',
                                        'target' => $container->getPid(),
                                        'update' => [
                                            'colPos' => $child['colPos'],
                                            'tx_container_parent' => $container->getUid(),
                                            'sys_language_uid' => $containerRecord['sys_language_uid'],

                                        ],
                                    ],
                                ],
                            ],
                        ];
                        $datahandler->start([], $cmdmap);
                        $datahandler->process_datamap();
                        $datahandler->process_cmdmap();
                    } else {
                        $cmdmap = [
                            'tt_content' => [
                                $child['uid'] => [
                                    'move' => [
                                        'action' => 'paste',
                                        'target' => -$prevChild['uid'],
                                        'update' => [
                                            'colPos' => $child['colPos'],
                                            'tx_container_parent' => $container->getUid(),
                                            'sys_language_uid' => $containerRecord['sys_language_uid'],

                                        ],
                                    ],
                                ],
                            ],
                        ];
                        $datahandler->start([], $cmdmap);
                        $datahandler->process_datamap();
                        $datahandler->process_cmdmap();
                    }
                    $prevChild = $child;
                }
            }
        }
    }
}
