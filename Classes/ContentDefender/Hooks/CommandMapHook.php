<?php

declare(strict_types=1);

namespace B13\Container\ContentDefender\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CommandMapHook
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
     * @param ContainerFactory|null $containerFactory
     * @param Registry|null $tcaRegistry
     * @param Database|null $database
     */
    public function __construct(ContainerFactory $containerFactory = null, Registry $tcaRegistry = null, Database $database = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
    }

    /**
     * @param DataHandler $dataHandler
     */
    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        if (!empty($dataHandler->cmdmap['tt_content'])) {
            foreach ($dataHandler->cmdmap['tt_content'] as $id => $cmds) {
                foreach ($cmds as $cmd => $data) {
                    if (
                        ($cmd === 'copy' || $cmd === 'move') &&
                        (!empty($data['update'])) &&
                        isset($data['update']['colPos']) &&
                        $data['update']['colPos'] > 0 &&
                        isset($data['update']['tx_container_parent']) &&
                        $data['update']['tx_container_parent'] > 0

                    ) {
                        try {
                            $record = $this->database->fetchOneRecord((int)$id);

                            $recordCType = $record['CType'];
                            $parent = (int)$data['update']['tx_container_parent'];
                            $colPos = (int)$data['update']['colPos'];
                            $container = $this->containerFactory->buildContainer($parent);
                            $cType = $container->getCType();
                            $allowedConfiguration = $this->tcaRegistry->getAllowedConfiguration($cType, $colPos);
                            foreach ($allowedConfiguration as $field => $value) {
                                $allowedValues = GeneralUtility::trimExplode(',', $value);
                                if (in_array($recordCType, $allowedValues) === false) {
                                    $msg = $recordCType . ' is not allowed in ' . $cType . ' on colPos ' . $colPos;
                                    $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $msg, '', FlashMessage::ERROR, true);
                                    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                                    $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
                                    $defaultFlashMessageQueue->enqueue($flashMessage);
                                    unset($dataHandler->cmdmap['tt_content'][$id][$cmd]);
                                    if (count($dataHandler->cmdmap['tt_content'][$id]) === 0) {
                                        unset($dataHandler->cmdmap['tt_content'][$id]);
                                    }
                                    continue;
                                }
                            }
                        } catch (Exception $e) {
                            // not a container
                        }
                    }
                }
            }
        }
    }
}
