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
     * Datahandler constructor.
     * @param ContainerFactory|null $containerFactory
     */
    public function __construct(ContainerFactory $containerFactory = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
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
    public function processCmdmap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        $dataHandler->cmdmap = $this->extractContainerIdFromColPosOnUpdate($dataHandler->cmdmap);
    }

    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        $dataHandler->datamap = $this->extractContainerIdFromColPosInDatamap($dataHandler->datamap);
    }

    /**
     * @param string $command
     * @param string $table
     * @param int $id
     * @param int $value
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @param $pasteUpdate
     * @param $pasteDatamap
     * @return void
     */
    public function processCmdmap_postProcess(string $command, string $table, int $id, int $value, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler, $pasteUpdate, $pasteDatamap): void
    {
        $language = null;
        if (!empty($pasteUpdate['sys_language_uid'])) {
            $language = (int)$pasteUpdate['sys_language_uid'];
        }
        if ($table === 'tt_content' && $command === 'copy' && !empty($pasteDatamap['tt_content'])) {
            $this->copyOrMoveChilds($id, $value, (int)array_key_first($pasteDatamap['tt_content']), $language,'copy', $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'move') {
            $this->copyOrMoveChilds($id, $value, $id, $language,'move', $dataHandler);
        } elseif ($table === 'tt_content' && $command === 'localize') {
            $this->localizeOrCopyToLanguage($id, $value, 'localize', $dataHandler);
        }
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
    protected function copyOrMoveChilds(int $origUid, int $newId, int $containerId, ?int $language, string $command, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler): void
    {
        try {
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
                if ($language !== null) {
                    $cmd['tt_content'][$record['uid']][$command]['update']['sys_language_uid'] = $language;
                }
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
                if (!empty($data['colPos'])) {
                    $colPos = $data['colPos'];
                    if (MathUtility::canBeInterpretedAsInteger($colPos) === false) {
                        [$containerId, $newColPos] = GeneralUtility::intExplode('-', $colPos);
                        $data['colPos'] = $newColPos;
                        $data['tx_container_parent'] = $containerId;
                    } elseif (!isset($data['tx_container_parent'])) {
                        $data['tx_container_parent'] = 0;
                    }
                }
            }
        }
        return $datamap;
    }

    /**
     * @param array $cmdmap
     */
    protected function extractContainerIdFromColPosOnUpdate(array $cmdmap): array
    {
        if (!empty($cmdmap['tt_content'])) {
            foreach ($cmdmap['tt_content'] as $id => &$cmds) {
                foreach ($cmds as &$cmd) {
                    if (!empty($cmd['update']) && !empty($cmd['update']['colPos'])) {
                        $colPos = $cmd['update']['colPos'];
                        if (MathUtility::canBeInterpretedAsInteger($colPos) === false) {
                            [$containerId, $newColPos] = GeneralUtility::intExplode('-', $colPos);
                            $cmd['update']['colPos'] = $newColPos;
                            $cmd['update']['tx_container_parent'] = $containerId;
                        } elseif (!isset($cmd['update']['tx_container_parent'])) {
                            $cmd['update']['tx_container_parent'] = 0;
                        }
                    }
                }
            }
        }
        return $cmdmap;
    }

}
