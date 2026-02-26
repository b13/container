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
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

#[Autoconfigure(public: true)]
class CommandMapPostProcessingHook
{
    public function __construct(protected ContainerFactory $containerFactory, protected ContainerService $containerService)
    {
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
        } elseif ($table === 'tt_content' && $command === 'localize') {
            $this->localizeChildren($id, (int)$value, $command, $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'copyToLanguage') {
            $this->copyToLanguageChildren($id, (int)$value, $command, $dataHandler);
        }
    }

    protected function copyToLanguageChildren(int $uid, int $language, string $command, DataHandler $dataHandler): void
    {
        try {
            $container = $this->containerFactory->buildContainer($uid);
            $last = $dataHandler->copyMappingArray['tt_content'][$uid] ?? null;
            $containerId = $last;
            $pos = $this->containerService->getAfterContainerElementTarget($container);
            // move next record after last child
            $cmd = ['tt_content' => [$last => [
                'move' => [
                    'target' => $pos,
                    'action' => 'paste',
                    'update' => [],
                ],
            ]]];
            $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $localDataHandler->enableLogging = $dataHandler->enableLogging;
            $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
            $localDataHandler->process_cmdmap();
            $children = $container->getChildRecords();
            foreach ($children as $record) {
                $cmd = ['tt_content' => [$record['uid'] => [$command => $language]]];
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->enableLogging = $dataHandler->enableLogging;
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
                $newId = $localDataHandler->copyMappingArray['tt_content'][$record['uid']] ?? null;
                if ($newId === null) {
                    continue;
                }
                $cmd = ['tt_content' => [$newId=> [
                    'move' => [
                        'target' => -$last,
                        'action' => 'paste',
                        'update' => [
                            'tx_container_parent' => $containerId,
                        ],
                    ],
                ]]];
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->enableLogging = $dataHandler->enableLogging;
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
                $last = $newId;
            }
        } catch (Exception $e) {
            // nothing todo
        }
    }

    protected function localizeChildren(int $uid, int $language, string $command, DataHandler $dataHandler): void
    {
        try {
            $container = $this->containerFactory->buildContainer($uid);
            $children = $container->getChildRecords();
            foreach ($children as $record) {
                $cmd = ['tt_content' => [$record['uid'] => [$command => $language]]];
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->enableLogging = $dataHandler->enableLogging;
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
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->lockContentElementRestrictions();
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
                $localDataHandler->enableLogging = $dataHandler->enableLogging;
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
                if (!isset($origCmdMap['tt_content'][$origUid][$command]['update']['sys_language_uid'])) {
                    continue;
                }
                if ((int)$origCmdMap['tt_content'][$origUid][$command]['update']['sys_language_uid'] === $record['sys_language_uid']) {
                    continue;
                }
                $target = -$record['uid'];
                // copy case
                $newId = $localDataHandler->copyMappingArray['tt_content'][$record['uid']] ?? null;
                if ($newId !== null) {
                    $target = -$newId;
                }
            }
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->endContainerProcess($origUid);
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->unlockContentElementRestrictions();
        } catch (Exception $e) {
            // nothing todo
        }
    }
}
