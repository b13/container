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
use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SortingInPage implements SingletonInterface
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

    /**
     * @var ContainerService
     */
    protected $containerService;

    protected $errors = [];

    public function __construct(Database $database, Registry $tcaRegistry, ContainerFactory $containerFactory, ContainerService $containerService)
    {
        $this->database = $database;
        $this->tcaRegistry = $tcaRegistry;
        $this->containerFactory = $containerFactory;
        $this->containerService = $containerService;
    }

    public function run(bool $dryRun = true, bool $enableLogging = false, ?int $pid = null): array
    {
        $this->unsetContentDefenderConfiguration();
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->enableLogging = $enableLogging;
        $cTypes = $this->tcaRegistry->getRegisteredCTypes();
        $containerUsedColPosArray = [];
        foreach ($cTypes as $cType) {
            $columns = $this->tcaRegistry->getAvailableColumns($cType);
            foreach ($columns as $column) {
                $containerUsedColPosArray[] = $column['colPos'];
            }
        }
        $rows = $this->database->getNonContainerChildrenPerColPos($containerUsedColPosArray, $pid);
        foreach ($rows as $recordsPerPageAndColPos) {
            $prevSorting = 0;
            $prevContainer = null;
            $prevChild = null;
            foreach ($recordsPerPageAndColPos as $record) {
                if (in_array($record['CType'], $cTypes, true)) {
                    $container = $this->containerFactory->buildContainer($record['uid']);
                    $children = $container->getChildRecords();
                    if (empty($children)) {
                        $sorting = $record['sorting'];
                    } else {
                        $lastChild = array_pop($children);
                        $sorting = $lastChild['sorting'];

                        if ($prevChild === null || $prevContainer === null) {
                            $prevChild = $lastChild;
                            $prevContainer = $container;
                            $prevSorting = $sorting;
                            continue;
                        }
                        $containerSorting = $container->getContainerRecord()['sorting'];
                        if ($containerSorting < $prevSorting) {
                            $this->errors[] = 'record ' . $record['uid'] . ' (' . $record['sorting'] . ')' .
                                ' on page ' . $record['pid'] .
                                ' should be sorted after last child ' . $prevChild['uid'] . ' (' . $prevChild['sorting'] . ')' .
                                ' of container ' . $prevContainer->getUid() . ' (' . $containerSorting . ')';
                            $this->moveRecordAfter((int)$record['uid'], $prevContainer->getUid(), $dryRun, $dataHandler);
                        }
                        $prevContainer = $container;
                        $prevChild = $lastChild;
                    }
                } else {
                    $sorting = $record['sorting'];
                }
                $prevSorting = $sorting;
            }
        }
        return $this->errors;
    }

    protected function moveRecordAfter(int $recordUid, int $moveUid, bool $dryRun, DataHandler $dataHandler): void
    {
        if ($dryRun === false) {
            $cmdmap = [
                'tt_content' => [
                    $recordUid => [
                        'move' => -1 * $moveUid,
                    ],
                ],
            ];
            $dataHandler->start([], $cmdmap);
            $dataHandler->process_datamap();
            $dataHandler->process_cmdmap();
        }
    }

    protected function unsetContentDefenderConfiguration(): void
    {
        // content_defender uses FormDataCompiler which expects a ServerRequest
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['content_defender'])) {
            unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['content_defender']);
        }
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['content_defender'])) {
            unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['content_defender']);
        }
    }
}
