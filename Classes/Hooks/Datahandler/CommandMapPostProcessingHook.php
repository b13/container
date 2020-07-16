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
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
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
    public function processCmdmap_postProcess(string $command, string $table, int $id, $value, DataHandler $dataHandler, $pasteUpdate, $pasteDatamap): void
    {
        if ($table === 'tt_content' && $command === 'copy' && !empty($pasteDatamap['tt_content'])) {
            $this->copyOrMoveChildren($id, (int)$value, (int)array_key_first($pasteDatamap['tt_content']), 'copy', $dataHandler);
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
    protected function localizeOrCopyToLanguage(int $uid, int $language, string $command, DataHandler $dataHandler): void
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
    protected function copyOrMoveChildren(int $origUid, int $newId, int $containerId, string $command, DataHandler $dataHandler): void
    {
        try {
            // when moving or copy a container into other language the other language is returned
            $container = $this->containerFactory->buildContainer($origUid);
            $children = array_reverse($container->getChildRecords());
            foreach ($children as $colPos => $record) {
                $cmd = [
                    'tt_content' => [
                        $record['uid'] => [
                            $command => [
                                'action' => 'paste',
                                'target' => $newId,
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
