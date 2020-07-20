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
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapHook
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
    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        if (is_array($dataHandler->datamap['tt_content'])) {
            foreach ($dataHandler->datamap['tt_content'] as $id => $values) {
                if (
                    isset($values['tx_container_parent']) &&
                    $values['tx_container_parent'] > 0 &&
                    isset($values['colPos']) &&
                    $values['colPos'] > 0
                ) {
                    if (isset($values['CType'])) {
                        $recordCType = $values['CType'];
                    } elseif (MathUtility::canBeInterpretedAsInteger($id)) {
                        $record = $this->database->fetchOneRecord((int)$id);
                        if ($record['sys_language_uid'] > 0 && $record['l18n_parent'] > 0) {
                            // use default language CType
                            $record = $this->database->fetchOneRecord((int)$record['l18n_parent']);
                        }
                        $recordCType = $record['CType'];
                    } else {
                        continue;
                    }
                    try {
                        $parent = (int)$values['tx_container_parent'];
                        $colPos = (int)$values['colPos'];
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
                                unset($dataHandler->datamap['tt_content'][$id]);
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
