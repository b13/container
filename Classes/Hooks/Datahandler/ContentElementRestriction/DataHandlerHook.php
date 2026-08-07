<?php

declare(strict_types=1);

namespace B13\Container\Hooks\Datahandler\ContentElementRestriction;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Hooks\Datahandler\DatahandlerProcess;
use B13\Container\Tca\Registry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayoutView;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

#[Autoconfigure(public: true)]
class DataHandlerHook
{
    protected $lockDatamapHook = false;

    public function __construct(
        private BackendLayoutView $backendLayoutView,
        private DatahandlerProcess $datahandlerProcess,
        private ContainerFactory $containerFactory,
        private Registry $registry
    ) {
    }

    public function processCmdmap_beforeStart(DataHandler $dataHandler): void
    {
        $cmdmap = $dataHandler->cmdmap;
        if (isset($cmdmap['pages'])) {
            $this->lockDatamapHook = true;
        }
        if (empty($cmdmap['tt_content']) || $dataHandler->bypassAccessCheckForRecords) {
            return;
        }
        $this->lockDatamapHook = true;
        if ($this->datahandlerProcess->areContentElementRestrictionsLooked()) {
            return;
        }
        foreach ($cmdmap['tt_content'] as $id => $incomingFieldArray) {
            foreach ($incomingFieldArray as $command => $value) {
                if (!in_array($command, ['copy', 'move'], true)) {
                    continue;
                }
                $currentRecord = BackendUtility::getRecord('tt_content', $id);

                // EXT:container start
                if (
                    (!empty($value['update'])) &&
                    isset($value['update']['colPos']) &&
                    $value['update']['colPos'] > 0 &&
                    isset($value['update']['tx_container_parent']) &&
                    $value['update']['tx_container_parent'] > 0 &&
                    MathUtility::canBeInterpretedAsInteger($id)
                ) {
                    $colPos = (int)$value['update']['colPos'];
                    if (!empty($currentRecord['CType'])) {
                        if ($this->checkContainerCType((int)$value['update']['tx_container_parent'], $currentRecord['CType'], (int)$value['update']['colPos']) === false) {
                            // Not allowed to move or copy to target. Unset this command and create a log entry which may be turned into a notification when called by BE.
                            unset($dataHandler->cmdmap['tt_content'][$id]);
                            $dataHandler->log('tt_content', $id, 1, null, 1, 'The command "%s" for record "tt_content:%s" with CType "%s" to colPos "%s" couldn\'t be executed due to disallowed value(s).', null, [$command, $id, $currentRecord['CType'], $colPos]);
                        }
                    }
                    $useChildId = null;
                    if ($command === 'move') {
                        $useChildId = $id;
                    }
                    if ($this->checkContainerMaxItems((int)$value['update']['tx_container_parent'], (int)$value['update']['colPos'], $useChildId)) {
                        unset($dataHandler->cmdmap['tt_content'][$id]);
                        $dataHandler->log('tt_content', $id, 1, null, 1, 'The command "%s" for record "tt_content:%s" to colPos "%s" couldn\'t be executed due to maxitems reached.', null, [$command, $id, $colPos]);
                    }
                    return;
                }
                // EXT:container end

                if (empty($currentRecord['CType'] ?? '')) {
                    continue;
                }
                if (is_array($value) && !empty($value['action']) && $value['action'] === 'paste' && isset($value['update']['colPos'])) {
                    // Moving / pasting to a new colPos on a potentially different page
                    $pageId = (int)$value['target'];
                    $colPos = (int)$value['update']['colPos'];
                } else {
                    $pageId = (int)$value;
                    $colPos = (int)$currentRecord['colPos'];
                }
                if ($pageId < 0) {
                    $targetRecord = BackendUtility::getRecord('tt_content', abs($pageId));
                    $pageId = (int)$targetRecord['pid'];
                    $colPos = (int)$targetRecord['colPos'];
                }

                $backendLayout = $this->backendLayoutView->getBackendLayoutForPage($pageId);
                $columnConfiguration = $this->backendLayoutView->getColPosConfigurationForPage($backendLayout, $colPos, $pageId);
                $allowedContentElementsInTargetColPos = GeneralUtility::trimExplode(',', $columnConfiguration['allowedContentTypes'] ?? '', true);
                $disallowedContentElementsInTargetColPos = GeneralUtility::trimExplode(',', $columnConfiguration['disallowedContentTypes'] ?? '', true);
                if ((!empty($allowedContentElementsInTargetColPos) && !in_array($currentRecord['CType'], $allowedContentElementsInTargetColPos, true))
                    || (!empty($disallowedContentElementsInTargetColPos) && in_array($currentRecord['CType'], $disallowedContentElementsInTargetColPos, true))
                ) {
                    // Not allowed to move or copy to target. Unset this command and create a log entry which may be turned into a notification when called by BE.
                    unset($dataHandler->cmdmap['tt_content'][$id]);
                    $dataHandler->log('tt_content', $id, 1, null, 1, 'The command "%s" for record "tt_content:%s" with CType "%s" to colPos "%s" couldn\'t be executed due to disallowed value(s).', null, [$command, $id, $currentRecord['CType'], $colPos]);
                }
            }
        }
    }

    protected function checkContainerMaxItems(int $containerId, int $colPos, ?int $childUid = null): bool
    {
        try {
            $container = $this->containerFactory->buildContainer($containerId);
            $columnConfiguration = $this->registry->getContentDefenderConfiguration($container->getCType(), $colPos);
            if (($columnConfiguration['maxitems'] ?? 0) === 0) {
                return false;
            }
            $childrenOfColumn = $container->getChildrenByColPos($colPos);
            $count = count($childrenOfColumn);
            if ($childUid !== null && $container->hasChildInColPos($colPos, $childUid)) {
                $count--;
            }
            return $count >= $columnConfiguration['maxitems'];
        } catch (Exception) {
            // not a container;
        }
        return false;
    }

    protected function checkContainerCType(int $containerId, string $cType, int $colPos): bool
    {
        try {
            $container = $this->containerFactory->buildContainer($containerId);
            $columnConfiguration = $this->registry->getContentDefenderConfiguration($container->getCType(), $colPos);
            $allowedContentElementsInTargetColPos = GeneralUtility::trimExplode(',', $columnConfiguration['allowedContentTypes'] ?? '', true);
            $disallowedContentElementsInTargetColPos = GeneralUtility::trimExplode(',', $columnConfiguration['disallowedContentTypes'] ?? '', true);
            if ((!empty($allowedContentElementsInTargetColPos) && !in_array($cType, $allowedContentElementsInTargetColPos, true))
                || (!empty($disallowedContentElementsInTargetColPos) && in_array($cType, $disallowedContentElementsInTargetColPos, true))
            ) {
                return false;
            }
        } catch (Exception) {
            // not a container;
        }
        return true;
    }

    public function processCmdmap_postProcess(string $command, string $table, $id, $value, DataHandler $dataHandler, $pasteUpdate, $pasteDatamap): void
    {
        $this->lockDatamapHook = false;
    }

    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        if ($this->lockDatamapHook === true) {
            return;
        }
        $datamap = $dataHandler->datamap;
        if (empty($datamap['tt_content']) || $dataHandler->bypassAccessCheckForRecords) {
            return;
        }
        foreach ($datamap['tt_content'] as $id => $incomingFieldArray) {
            if (MathUtility::canBeInterpretedAsInteger($id)) {
                $record = BackendUtility::getRecord('tt_content', $id);
                if (!is_array($record)) {
                    // Skip this if the record could not be determined for whatever reason
                    continue;
                }
                $recordData = array_merge($record, $incomingFieldArray);
            } else {
                $recordData = array_merge($dataHandler->defaultValues['tt_content'] ?? [], $incomingFieldArray);
            }
            // EXT:container start
            if ((int)($recordData['tx_container_parent'] ?? 0) > 0 && (int)($recordData['colPos'] ?? 0) > 0) {
                if ($this->checkContainerMaxItems((int)$recordData['tx_container_parent'], (int)$recordData['colPos'])) {
                    if (MathUtility::canBeInterpretedAsInteger($id)) {
                        // edit
                        continue;
                    }
                    unset($dataHandler->datamap['tt_content'][$id]);
                    $dataHandler->log('tt_content', $id, 1, null, 1, 'The command "%s" for record "tt_content:%s" to colPos "%s" couldn\'t be executed due to maxitems reached.', null, [$id, $recordData['colPos']]);
                }
            }
            // EXT:container end
            if (empty($recordData['CType']) || !array_key_exists('colPos', $recordData)) {
                // No idea what happened here, but we stop with this record if there is no CType or colPos
                continue;
            }
            $pageId = (int)$recordData['pid'];
            if ($pageId < 0) {
                $previousRecord = BackendUtility::getRecord('tt_content', abs($pageId), 'pid');
                if ($previousRecord === null) {
                    // Broken target data. Stop here and let DH handle this mess.
                    continue;
                }
                $pageId = (int)$previousRecord['pid'];
            }
            $colPos = (int)$recordData['colPos'];
            $backendLayout = $this->backendLayoutView->getBackendLayoutForPage($pageId);
            $columnConfiguration = $this->backendLayoutView->getColPosConfigurationForPage($backendLayout, $colPos, $pageId);
            $allowedContentElementsInTargetColPos = GeneralUtility::trimExplode(',', $columnConfiguration['allowedContentTypes'] ?? '', true);
            $disallowedContentElementsInTargetColPos = GeneralUtility::trimExplode(',', $columnConfiguration['disallowedContentTypes'] ?? '', true);
            if ((!empty($allowedContentElementsInTargetColPos) && !in_array($recordData['CType'], $allowedContentElementsInTargetColPos, true))
                || (!empty($disallowedContentElementsInTargetColPos) && in_array($recordData['CType'], $disallowedContentElementsInTargetColPos, true))
            ) {
                // Not allowed to create in this colPos on this page. Unset this command and create a log entry which may be turned into a notification when called by BE.
                unset($dataHandler->datamap['tt_content'][$id]);
                $dataHandler->log('tt_content', $id, 1, null, 1, 'The record "tt_content:%s" with CType "%s" in colPos "%s" couldn\'t be saved due to disallowed value(s).', null, [$id, $recordData['CType'], $colPos]);
            }
        }
    }
}
