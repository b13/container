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
        $this->extractContainerIdFromColPos($dataHandler->cmdmap);
    }

    /**
     * @param array $cmdmap
     */
    protected function extractContainerIdFromColPos(array &$cmdmap): void
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
                        }
                    }
                }
            }
        }
    }

}
