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
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Sorting implements SingletonInterface
{

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    protected $errors = [];

    public function __construct(Database $database = null, Registry $tcaRegistry = null, ContainerFactory $containerFactory = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
    }

    public function run(bool $dryRun = true): array
    {
        $cTypes = $this->tcaRegistry->getRegisteredCTypes();
        $containerRecords = $this->database->getContainerRecords($cTypes);
        $containerRecords = array_merge($containerRecords, $this->database->getContainerRecordsFreeMode($cTypes));
        $colPosByCType = [];
        foreach ($cTypes as $cType) {
            $columns = $this->tcaRegistry->getAvailableColumns($cType);
            $colPosByCType[$cType] = [];
            foreach ($columns as $column) {
                $colPosByCType[$cType][] = $column['colPos'];
            }
        }
        $this->fixChildrenSorting($containerRecords, $colPosByCType, $dryRun);
        // todo not required ?
        //$this->fixElementAfterContainerSorting($containerRecords, $colPosByCType, $dryRun);
        return $this->errors;
    }

    protected function fixElementAfterContainerSorting(array $containerRecords, array $colPosByCType, bool $dryRun): void
    {
        foreach ($containerRecords as $containerRecord) {
            $nextElement = $this->database->getContentElementAfter($containerRecord);
            if ($nextElement !== null) {
                try {
                    $container = $this->containerFactory->buildContainer($containerRecord['uid']);
                } catch (Exception $e) {
                    // should not happend
                    continue;
                }
                $lastChild = null;
                foreach ($colPosByCType[$containerRecord['CType']] as $colPos) {
                    $children = $container->getChildrenByColPos($colPos);
                    foreach ($children as $child) {
                        $lastChild = $child;
                    }
                }
                if ($lastChild !== null && $lastChild['sorting'] > $nextElement['sorting']) {
                    $this->errors[] = 'container uid: ' . $containerRecord['uid'] . ', pid ' . $containerRecord['pid'] . ' must be fixed';
                }
            }
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
                    $this->errors[] = 'container uid: ' . $containerRecord['uid'] . ', pid ' . $containerRecord['pid'] . ' must be fixed';
                    return true;
                }
                $prevSorting = $child['sorting'];
            }
        }
        return false;
    }

    protected function fixChildrenSorting(array $containerRecords, array $colPosByCType, bool $dryRun): void
    {
        $datahandler = GeneralUtility::makeInstance(DataHandler::class);
        $datahandler->enableLogging = false;
        foreach ($containerRecords as $containerRecord) {
            try {
                $container = $this->containerFactory->buildContainer($containerRecord['uid']);
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
                                            'colPos' => $container->getUid() . '-' . $child['colPos'],
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
                                            'colPos' => $container->getUid() . '-' . $child['colPos'],
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
