<?php

namespace  B13\Container\Hooks;

use B13\Container\ContainerLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class UsedRecords
{

    public function addContainerChilds(array $params, PageLayoutView $pageLayoutView): bool
    {
        $record = $params['record'];
        // TODO check also colPos
        if ($record['tx_container_parent'] > 0) {
            return true;
        } else {
            return $params['used'];
        }

    }
}
