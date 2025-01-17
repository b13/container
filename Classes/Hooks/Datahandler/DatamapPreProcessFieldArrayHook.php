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
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapPreProcessFieldArrayHook
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var ContainerService
     */
    protected $containerService;

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(
        ContainerFactory $containerFactory,
        Database $database,
        Registry $tcaRegistry,
        ContainerService $containerService
    ) {
        $this->containerFactory = $containerFactory;
        $this->database = $database;
        $this->tcaRegistry = $tcaRegistry;
        $this->containerService = $containerService;
    }

    protected function newElementAfterContainer(array $incomingFieldArray): array
    {
        $record = $this->database->fetchOneRecord(-(int)$incomingFieldArray['pid']);
        if ($record === null) {
            // new elements in container have already correct target
            return $incomingFieldArray;
        }
        if (
            (int)$record['uid'] === (int)($incomingFieldArray['tx_container_parent'] ?? 0) ||
            (isset($record['t3_origuid']) && $record['t3_origuid'] > 0 && (int)$record['t3_origuid'] === (int)($incomingFieldArray['tx_container_parent'] ?? 0))
        ) {
            return $incomingFieldArray;
        }
        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
            return $incomingFieldArray;
        }
        try {
            $container = $this->containerFactory->buildContainer((int)$record['uid']);
            if ($container->getLanguage() === 0 || !$container->isConnectedMode()) {
                $incomingFieldArray['pid'] = $this->containerService->getAfterContainerElementTarget($container);
            }
        } catch (Exception $e) {
        }
        return $incomingFieldArray;
    }

    protected function copyToLanguageElementInContainer(array $incomingFieldArray): array
    {
        if (!isset($incomingFieldArray['tx_container_parent']) || (int)$incomingFieldArray['tx_container_parent'] === 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['l10n_source']) || (int)$incomingFieldArray['l10n_source'] === 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['l18n_parent']) || (int)$incomingFieldArray['l18n_parent'] > 0) {
            return $incomingFieldArray;
        }
        if (!isset($incomingFieldArray['sys_language_uid']) || (int)$incomingFieldArray['sys_language_uid'] === 0) {
            return $incomingFieldArray;
        }
        $record = $this->database->fetchOneRecord(-$incomingFieldArray['pid']);
        $translatedContainerRecord = $this->database->fetchOneTranslatedRecordByl10nSource((int)$incomingFieldArray['tx_container_parent'], (int)$incomingFieldArray['sys_language_uid']);
        if ($record === null || $translatedContainerRecord === null) {
            return $incomingFieldArray;
        }
        try {
            $incomingFieldArray['tx_container_parent'] = $translatedContainerRecord['uid'];
            $container = $this->containerFactory->buildContainer((int)$translatedContainerRecord['uid']);
            if ((int)$record['sys_language_uid'] === 0 || empty($container->getChildrenByColPos((int)$incomingFieldArray['colPos']))) {
                $target = $this->containerService->getNewContentElementAtTopTargetInColumn($container, (int)$incomingFieldArray['colPos']);
                $incomingFieldArray['pid'] = $target;
            }
        } catch (Exception $e) {
            // not a container
        }
        return $incomingFieldArray;
    }

    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, $id, DataHandler $dataHandler): void
    {
        if ($table !== 'tt_content') {
            return;
        }
        if (MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }
        if (!isset($incomingFieldArray['pid']) || (int)$incomingFieldArray['pid'] >= 0) {
            return;
        }
        $incomingFieldArray = $this->newElementAfterContainer($incomingFieldArray);
        $incomingFieldArray = $this->copyToLanguageElementInContainer($incomingFieldArray);
    }
}
