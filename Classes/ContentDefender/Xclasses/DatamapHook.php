<?php

declare(strict_types=1);

namespace B13\Container\ContentDefender\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\ContentDefender\ContainerColumnConfigurationService;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Hooks\Datahandler\DatahandlerProcess;
use IchHabRecht\ContentDefender\Hooks\DatamapDataHandlerHook;
use IchHabRecht\ContentDefender\Repository\ContentRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapHook extends DatamapDataHandlerHook
{
    /**
     * @var ContainerColumnConfigurationService
     */
    protected $containerColumnConfigurationService;

    /**
     * @var Database
     */
    protected $database;

    protected $mapping = [];

    public function __construct(
        ContentRepository $contentRepository = null,
        ContainerColumnConfigurationService $containerColumnConfigurationService = null,
        Database $database = null
    ) {
        $this->containerColumnConfigurationService = $containerColumnConfigurationService ?? GeneralUtility::makeInstance(ContainerColumnConfigurationService::class);
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        parent::__construct($contentRepository);
    }

    /**
     * @param DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        if (is_array($dataHandler->datamap['tt_content'] ?? null)) {
            foreach ($dataHandler->datamap['tt_content'] as $id => $values) {
                if (
                    isset($values['tx_container_parent']) &&
                    $values['tx_container_parent'] > 0 &&
                    isset($values['colPos']) &&
                    $values['colPos'] > 0
                ) {
                    // no maxitems check for localized records
                    if (isset($values['l18n_parent'])) {
                        if ((int)$values['l18n_parent'] !== 0) {
                            continue;
                        }
                    } elseif (MathUtility::canBeInterpretedAsInteger($id)) {
                        $record = $this->database->fetchOneRecord((int)$id);
                        if (isset($record['l18n_parent']) && (int)$record['l18n_parent'] !== 0) {
                            continue;
                        }
                    }
                    $containerId = (int)$values['tx_container_parent'];
                    // copyToLanguage case
                    if ((int)($values['l18n_parent'] ?? 1) === 0 &&
                        (int)($values['l10n_source'] ?? 0) > 0 &&
                        (int)($values['sys_language_uid'] ?? 0) > 0
                    ) {
                        // free mode language CE used, we have to consider free mode container
                        $containerRecord = $this->database->fetchContainerRecordLocalizedFreeMode($containerId, (int)$values['sys_language_uid']);
                        if ($containerRecord !== null) {
                            $containerId = (int)$containerRecord['uid'];
                        }
                    }
                    $useChildId = null;
                    $colPos = (int)$values['colPos'];
                    if (MathUtility::canBeInterpretedAsInteger($id)) {
                        $this->mapping[(int)$id] = [
                            'containerId' => (int)$values['tx_container_parent'],
                            'colPos' => (int)$values['colPos'],
                        ];
                        $useChildId = $id;
                    } else {
                        // new elements (first created in origin container/colPos, so we check the real target)
                        $targetColPos = $this->containerColumnConfigurationService->getTargetColPosForNew($containerId, (int)$values['colPos']);
                        if ($targetColPos !== null) {
                            $colPos = $targetColPos;
                        }
                        $containerIdTarget = $this->containerColumnConfigurationService->getContainerIdForNew($containerId, (int)$values['colPos']);
                        if ($containerIdTarget !== null) {
                            $containerId = $containerIdTarget;
                        }
                    }
                    if ($this->containerColumnConfigurationService->isMaxitemsReachedByContainenrId($containerId, $colPos, $useChildId)) {
                        unset($dataHandler->datamap['tt_content'][$id]);
                        $dataHandler->log(
                            'tt_content',
                            $id,
                            1,
                            0,
                            1,
                            'The command couldn\'t be executed due to reached maxitems configuration',
                            28
                        );
                    }
                }
            }
        }
        parent::processDatamap_beforeStart($dataHandler);
    }

    protected function isRecordAllowedByRestriction(array $columnConfiguration, array $record): bool
    {
        if (
            isset($record['tx_container_parent']) &&
            $record['tx_container_parent'] > 0 &&
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->isContainerInProcess((int)$record['tx_container_parent'])
        ) {
            return true;
        }
        if (isset($this->mapping[$record['uid']])) {
            $columnConfiguration = $this->containerColumnConfigurationService->override(
                $columnConfiguration,
                $this->mapping[$record['uid']]['containerId'],
                $this->mapping[$record['uid']]['colPos']
            );
        } elseif (isset($record['tx_container_parent']) && $record['tx_container_parent'] > 0) {
            $copyMapping = $this->containerColumnConfigurationService->getCopyMappingByOrigUid((int)($record['t3_origuid'] ?? 0));
            if ($copyMapping !== null) {
                $columnConfiguration = $this->containerColumnConfigurationService->override(
                    $columnConfiguration,
                    $copyMapping['tx_container_parent'],
                    $copyMapping['colPos']
                );
            } else {
                $columnConfiguration = $this->containerColumnConfigurationService->override(
                    $columnConfiguration,
                    (int)$record['tx_container_parent'],
                    (int)$record['colPos']
                );
            }
        }
        return parent::isRecordAllowedByRestriction($columnConfiguration, $record);
    }

    protected function isRecordAllowedByItemsCount(array $columnConfiguration, array $record): bool
    {
        if (isset($record['tx_container_parent']) &&
            $record['tx_container_parent'] > 0 &&
            (GeneralUtility::makeInstance(DatahandlerProcess::class))->isContainerInProcess((int)$record['tx_container_parent'])) {
            return true;
        }
        if (isset($this->mapping[$record['uid']])) {
            return true;
        }
        return parent::isRecordAllowedByItemsCount($columnConfiguration, $record);
    }
}
