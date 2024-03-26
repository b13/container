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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class CommandMapPostProcessingHook
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    public function __construct(ContainerFactory $containerFactory)
    {
        $this->containerFactory = $containerFactory;
    }

    public function processCmdmap_postProcess(string $command, string $table, $id, $value, DataHandler $dataHandler, $pasteUpdate, $pasteDatamap): void
    {
        if (!MathUtility::canBeInterpretedAsInteger($id) || (int)$id === 0) {
            return;
        }
        $id = (int)$id;
        if ($table === 'tt_content' && $command === 'copy' && !empty($pasteDatamap['tt_content'])) {
            $this->copyOrMoveChildren($id, (int)$value, (int)$dataHandler->copyMappingArray['tt_content'][$id], 'copy', $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'move') {
            $this->copyOrMoveChildren($id, (int)$value, $id, 'move', $dataHandler);
        } elseif ($table === 'tt_content' && ($command === 'localize' || $command === 'copyToLanguage')) {
            $this->localizeOrCopyToLanguage($id, (int)$value, $command, $dataHandler);
        }
    }

    protected function localizeOrCopyToLanguage(int $uid, int $language, string $command, DataHandler $dataHandler): void
    {
        try {
            $container = $this->containerFactory->buildContainer($uid);
            $children = $container->getChildRecords();
            $children = array_reverse($children);
            $cmd = ['tt_content' => []];
            foreach ($children as $colPos => $record) {
                $cmd['tt_content'][$record['uid']] = [$command => $language];
            }
            if (count($cmd['tt_content']) > 0) {
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
            }
        } catch (Exception $e) {
            // nothing todo
        }
    }

    protected function copyOrMoveChildren(int $origUid, int $newId, int $containerId, string $command, DataHandler $dataHandler): void
    {
        try {
            // when moving or copy a container into other language the other language is returned
            $container = $this->containerFactory->buildContainer($origUid);
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->startContainerProcess($origUid);
            $children = [];
            $colPosVals = $container->getChildrenColPos();
            foreach ($colPosVals as $colPos) {
                $childrenByColPos = $container->getChildrenByColPos($colPos);
                $childrenByColPos = array_reverse($childrenByColPos);
                foreach ($childrenByColPos as $child) {
                    $children[] = $child;
                }
            }
            if ($newId < 0) {
                $previousRecord = BackendUtility::getRecord('tt_content', abs($newId), 'pid');
                $target = (int)$previousRecord['pid'];
            } else {
                $target = $newId;
            }
            foreach ($children as $record) {
                $cmd = [
                    'tt_content' => [
                        $record['uid'] => [
                            $command => [
                                'action' => 'paste',
                                'target' => $target,
                                'update' => [
                                    'tx_container_parent' => $containerId,
                                    'colPos' => $record['colPos'],
                                ],
                            ],
                        ],
                    ],
                ];
                $origCmdMap = $dataHandler->cmdmap;
                if (isset($origCmdMap['tt_content'][$origUid][$command]['update']['sys_language_uid'])) {
                    $cmd['tt_content'][$record['uid']][$command]['update']['sys_language_uid'] = $origCmdMap['tt_content'][$origUid][$command]['update']['sys_language_uid'];
                }
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
            }
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->endContainerProcess($origUid);
        } catch (Exception $e) {
            // nothing todo
        }
    }
}
