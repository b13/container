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
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapBeforeStartHook
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
     * @param ContainerFactory|null $containerFactory
     * @param Database|null $database
     */
    public function __construct(ContainerFactory $containerFactory = null, Database $database = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
    }

    /**
     * @param DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        // ajax move (drag & drop)
        $dataHandler->datamap = $this->extractContainerIdFromColPosInDatamap($dataHandler->datamap);
        $dataHandler->datamap = $this->datamapForChildLocalizations($dataHandler->datamap);
        $dataHandler->datamap = $this->datamapForChildrenChangeContainerLanguage($dataHandler->datamap);
    }

    /**
     * @param array $datamap
     * @return array
     */
    protected function extractContainerIdFromColPosInDatamap(array $datamap): array
    {
        if (!empty($datamap['tt_content'])) {
            foreach ($datamap['tt_content'] as $id => &$data) {
                if (isset($data['colPos'])) {
                    $colPos = $data['colPos'];
                    if (MathUtility::canBeInterpretedAsInteger($colPos) === false) {
                        [$containerId, $newColPos] = GeneralUtility::intExplode('-', $colPos);
                        $data['colPos'] = $newColPos;
                        $data['tx_container_parent'] = $containerId;
                    } elseif (!isset($data['tx_container_parent'])) {
                        $data['tx_container_parent'] = 0;
                        $data['colPos'] = (int)$colPos;
                    }
                }
            }
        }
        return $datamap;
    }

    /**
     * @param array $datamap
     * @return array
     */
    protected function datamapForChildLocalizations(array $datamap): array
    {
        $datamapForLocalizations = ['tt_content' => []];
        if (!empty($datamap['tt_content'])) {
            foreach ($datamap['tt_content'] as $id => &$data) {
                if (isset($data['colPos'])) {
                    $record = $this->database->fetchOneRecord((int)$id);
                    if ($record !== null &&
                        $record['sys_language_uid'] === 0 &&
                        (
                            $record['tx_container_parent'] > 0
                            || (isset($data['tx_container_parent']) && $data['tx_container_parent'] > 0)
                        )
                    ) {
                        $translations = $this->database->fetchOverlayRecords($record);
                        foreach ($translations as $translation) {
                            $datamapForLocalizations['tt_content'][$translation['uid']] = [
                                'colPos' => $data['colPos']
                            ];
                            if (isset($data['tx_container_parent'])) {
                                $datamapForLocalizations['tt_content'][$translation['uid']]['tx_container_parent'] = $data['tx_container_parent'];
                            }
                        }
                    }
                }
            }
        }
        if (count($datamapForLocalizations['tt_content']) > 0) {
            $datamap['tt_content'] = array_replace($datamap['tt_content'], $datamapForLocalizations['tt_content']);
        }
        return $datamap;
    }

    /**
     * @param array $datamap
     * @return array
     */
    protected function datamapForChildrenChangeContainerLanguage(array $datamap): array
    {
        $datamapForLocalizations = ['tt_content' => []];
        if (!empty($datamap['tt_content'])) {
            foreach ($datamap['tt_content'] as $id => $data) {
                if (isset($data['sys_language_uid'])) {
                    try {
                        $container = $this->containerFactory->buildContainer((int)$id);
                        $children = $container->getChildRecords();
                        foreach ($children as $child) {
                            if ((int)$child['sys_language_uid'] !== (int)$data['sys_language_uid']) {
                                $datamapForLocalizations['tt_content'][$child['uid']] = [
                                    'sys_language_uid' => $data['sys_language_uid']
                                ];
                            }
                        }
                    } catch (Exception $e) {
                        // nothing todo
                    }
                }
            }
        }
        if (count($datamapForLocalizations['tt_content']) > 0) {
            $datamap['tt_content'] = array_replace($datamap['tt_content'], $datamapForLocalizations['tt_content']);
        }
        return $datamap;
    }
}
