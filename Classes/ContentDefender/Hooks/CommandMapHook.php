<?php

namespace  B13\Container\ContentDefender\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Exception;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Domain\Factory\ContainerFactory;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class CommandMapHook
{
    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory = null;

    /**
     * UsedRecords constructor.
     * @param ContainerFactory|null $containerFactory
     * @param Registry|null $tcaRegistry
     */
    public function __construct(ContainerFactory $containerFactory = null, Registry $tcaRegistry = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }
    /**
     * @param DataHandler $dataHandler
     */
    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        if (!empty($dataHandler->cmdmap['tt_content'])) {
            foreach ($dataHandler->cmdmap['tt_content'] as $id => $cmds) {
                #var_dump($id);
                #var_dump($dataHandler->datamap);
                foreach ($cmds as $cmd => $data) {
                    #var_dump($cmd);
                    #var_dump($data);
                    #var_dump($dataHandler->datamap['tt_content']);
                    if (!empty($data['update'])) {

                    } elseif ($dataHandler->datamap['tt_content'][$id]) {
                        #var_dump($dataHandler->datamap['tt_content'][$id]);
                    }

                    if (
                        ($cmd === 'copy' || $cmd === 'move') &&
                        (!empty($data['update'])) &&
                        isset($data['update']['colPos']) &&
                        $data['update']['colPos'] > 0 &&
                        isset($data['update']['tx_container_parent']) &&
                        $data['update']['tx_container_parent'] > 0

                    ) {
                        try {
                            $record = BackendUtility::getRecord('tt_content', $id);
                            $recordCType = $record['CType'];
                            $parent = (int)$data['update']['tx_container_parent'];
                            $colPos = (int)$data['update']['colPos'];
                            $container = $this->containerFactory->buildContainer($parent);
                            $cType = $container->getCType();
                            $allowedConfiguration = $this->tcaRegistry->getAllowedConfiguration($cType, $colPos);
                            $allowed = true;
                            foreach ($allowedConfiguration as $field => $value) {
                                $allowedValues = GeneralUtility::trimExplode(',', $value);
                                if (in_array($recordCType, $allowedValues) === false) {
                                    $allowed = false;
                                }
                            }
                            if ($allowed === false) {
                                unset($dataHandler->cmdmap['tt_content'][$id][$cmd]);
                                if (count($dataHandler->cmdmap['tt_content'][$id]) === 0) {
                                    unset($dataHandler->cmdmap['tt_content'][$id]);
                                }
                                $msg = 'not allowed';
                                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $msg, '', FlashMessage::ERROR, true);
                                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                                $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
                                $defaultFlashMessageQueue->enqueue($flashMessage);
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
