<?php

namespace  B13\Container\Hooks;


use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use B13\Container\Domain\Factory\ContainerFactory;

class Datahandler
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory = null;

    /**
     * @var DatahandlerDatabase
     */
    protected $dataHandlerDatabase = null;

    /**
     * @param ContainerFactory|null $containerFactory
     * @param DatahandlerDatabase|null $datahandlerDatabase
     */
    public function __construct(ContainerFactory $containerFactory = null, DatahandlerDatabase $datahandlerDatabase = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->dataHandlerDatabase = $datahandlerDatabase ?? GeneralUtility::makeInstance(DatahandlerDatabase::class);
    }

    /*
     * processDatamap_beforeStart
     * processDatamap_preProcessFieldArray
     * processDatamap_postProcessFieldArray
     * processDatamap_afterDatabaseOperations
     * processDatamap_afterAllOperations
     *
     * processCmdmap_beforeStart
     * processCmdmap_preProcess
     * processCmdmap_postProcess
     * processCmdmap_afterFinish
     *
     * processCmdmap_deleteAction
     * moveRecord
     * processCmdmap_deleteAction
     *
     */

    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        // ajax move (drag & drop)
        $dataHandler->datamap = $this->extractContainerIdFromColPosInDatamap($dataHandler->datamap);
        $dataHandler->datamap = $this->datamapForChildLocalizations($dataHandler->datamap);
        $dataHandler->datamap = $this->datamapForChildsChangeContainerLanguage($dataHandler->datamap);
    }


    /**
     * @param string $command
     * @param string $table
     * @param int $id
     * @param mixed $value
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @param $pasteUpdate
     * @param $pasteDatamap
     * @return void
     */
    public function processCmdmap_postProcess(string $command, string $table, int $id, $value, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler, $pasteUpdate, $pasteDatamap): void
    {
        if (is_array($value)) {
            // WS
            #$value = ["action"]=> string(3) "new" ["label"]=> string(22) "Auto-created for WS #1" }
            return;
        }
        if ($table === 'tt_content' && $command === 'copy' && !empty($pasteDatamap['tt_content'])) {
            $this->copyOrMoveChilds($id, $value, (int)array_key_first($pasteDatamap['tt_content']),'copy', $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'move') {
            $this->copyOrMoveChilds($id, $value, $id,'move', $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'localize') {
            $this->localizeOrCopyToLanguage($id, $value, 'localize', $dataHandler);
        }
    }

    /**
     * @param string $table
     * @param int $id
     * @param array $recordToDelete
     * @param bool $recordWasDeleted
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processCmdmap_deleteAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        if ($table === 'tt_content') {
            try {
                $container = $this->containerFactory->buildContainer($id);
                $childs = $container->getChildRecords();
                $toDelete = [];
                foreach ($childs as $colPos => $record) {
                    $toDelete[$record['uid']] = ['delete' => 1];
                }
                if (count($toDelete) > 0) {
                    $cmd = ['tt_content' => $toDelete];
                    $localDataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
                    $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                    $localDataHandler->process_cmdmap();
                }
            } catch (Exception $e) {
                // nothing todo
            }
        }
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
                    $record = $this->dataHandlerDatabase->fetchOneRecord((int)$id);
                    if ($record !== null &&
                        $record['sys_language_uid'] === 0 &&
                        (
                            $record['tx_container_parent'] > 0
                            || (isset($data['tx_container_parent']) && $data['tx_container_parent'] > 0)
                        )
                    ) {
                        $translations = $this->dataHandlerDatabase->fetchOverlayRecords($record);
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
    protected function datamapForChildsChangeContainerLanguage(array $datamap): array
    {
        $datamapForLocalizations = ['tt_content' => []];
        if (!empty($datamap['tt_content'])) {
            foreach ($datamap['tt_content'] as $id => $data) {
                if (isset($data['sys_language_uid'])) {
                    try {
                        $container = $this->containerFactory->buildContainer((int)$id);
                        $childs = $container->getChildRecords();
                        foreach ($childs as $child) {
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

    /**
     * @param int $uid
     * @param int $language
     * @param string $command
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return void
     */
    protected function localizeOrCopyToLanguage(int $uid, int $language, string $command, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        try {
            $container = $this->containerFactory->buildContainer($uid);
            $childs = $container->getChildRecords();
            $cmd = ['tt_content' => []];
            foreach ($childs as $colPos => $record) {
                $cmd['tt_content'][$record['uid']] = [$command => $language];
            }
            if (count($cmd['tt_content']) > 0) {
                $localDataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
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
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return void
     */
    protected function copyOrMoveChilds(int $origUid, int $newId, int $containerId, string $command, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        try {
            // when moving or copy a container into other language the other language is returned
            $container = $this->containerFactory->buildContainer($origUid);
            $childs = $container->getChildRecords();
            $cmd = ['tt_content' => []];
            foreach ($childs as $colPos => $record) {
                $cmd['tt_content'][$record['uid']] = [
                    $command => [
                        'action' => 'paste',
                        'target' => $newId,
                        'update' => [
                            'tx_container_parent' => $containerId,
                            'colPos' =>  $record['colPos']
                        ]
                    ]
                ];
            }
            if (count($cmd['tt_content']) > 0) {
                $localDataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
            }
        } catch (Exception $e) {
            // nothing todo
        }
    }
}
