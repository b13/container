<?php

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

class CommandMapPostProcessingHook
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @param ContainerFactory|null $containerFactory
     */
    public function __construct(ContainerFactory $containerFactory = null)
    {
        if ($containerFactory === null) {
            $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        }
        $this->containerFactory = $containerFactory;
    }

    /**
     * @param string $command
     * @param string $table
     * @param int $id
     * @param mixed $value
     * @param DataHandler $dataHandler
     * @param mixed $pasteUpdate
     * @param mixed $pasteDatamap
     */
    public function processCmdmap_postProcess($command, $table, $id, $value, DataHandler $dataHandler, $pasteUpdate, $pasteDatamap)
    {
        if ($table === 'tt_content' && $command === 'copy' && !empty($pasteDatamap['tt_content'])) {
            $arrKeys = array_keys($pasteDatamap['tt_content']);
            $this->copyOrMoveChildren($id, (int)$value, (int)$arrKeys[0], 'copy', $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'move') {
            $this->copyOrMoveChildren($id, (int)$value, $id, 'move', $dataHandler);
        } elseif ($table === 'tt_content' && ($command === 'localize' || $command === 'copyToLanguage')) {
            $this->localizeOrCopyToLanguage($id, (int)$value, $command, $dataHandler);
        }
    }

    /**
     * @param int $uid
     * @param int $language
     * @param string $command
     * @param DataHandler $dataHandler
     */
    protected function localizeOrCopyToLanguage($uid, $language, $command, DataHandler $dataHandler)
    {
        try {
            $container = $this->containerFactory->buildContainer($uid);
            $children = $container->getChildRecords();
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

    /**
     * @param int $origUid
     * @param int $newId
     * @param int $containerId
     * @param string $command
     * @param DataHandler $dataHandler
     */
    protected function copyOrMoveChildren($origUid, $newId, $containerId, $command, DataHandler $dataHandler)
    {
        try {
            #var_dump($origUid);
            // when moving or copy a container into other language the other language is returned
            $container = $this->containerFactory->buildContainer($origUid);
            #var_dump('abc');
            $children = array_reverse($container->getChildRecords());
            if ($newId < 0) {
                $previousRecord = BackendUtility::getRecord('tt_content', abs($newId), 'pid');
                $target = (int)$previousRecord['pid'];
            } else {
                $target = $newId;
            }
            foreach ($children as $colPos => $record) {
                $cmd = [
                    'tt_content' => [
                        $record['uid'] => [
                            $command => [
                                'action' => 'paste',
                                'target' => $target,
                                'update' => [
                                    'tx_container_parent' => $containerId,
                                    'colPos' => $record['colPos']
                                ]
                            ]
                        ]
                    ]
                ];
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
            }
        } catch (Exception $e) {
            // nothing todo
        }
    }
}
