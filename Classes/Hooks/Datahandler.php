<?php

namespace  B13\Container\Hooks;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class Datahandler
{

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

    protected function extractContainerIdFromColPosInDatamap(array $datamap): array
    {
        if (!empty($datamap['tt_content'])) {
            foreach ($datamap['tt_content'] as $id => &$data) {
                if (!empty($data['colPos'])) {
                    $colPos = $data['colPos'];
                    if (MathUtility::canBeInterpretedAsInteger($colPos) === false) {
                        list($containerId, $newColPos) = GeneralUtility::intExplode('-', $colPos);
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
                            list($containerId, $newColPos) = GeneralUtility::intExplode('-', $colPos);
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
