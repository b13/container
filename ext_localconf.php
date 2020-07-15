<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(static function () {
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
        \TYPO3\CMS\Backend\Utility\BackendUtility::class,
        'getPagesTSconfigPreInclude',
        B13\Container\Tca\Registry::class,
        'addPageTS'
    );

    // draw container grid
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] =
        \B13\Container\Hooks\DrawItem::class;

    // register icons
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['tx_container'] =
        \B13\Container\Hooks\TableConfigurationPostProcessing::class;

    // remove container colPos from "unused" page-elements
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used']['tx_container'] =
        \B13\Container\Hooks\UsedRecords::class . '->addContainerChildren';

    // add tx_container_parent parameter to wizard items
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['tx_container'] =
        \B13\Container\Hooks\WizardItems::class;

    // datahandler hooks
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_container-post-process'] =
        \B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_container-before-start'] =
        \B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook::class;

    // before workspace hook, which delete container record
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'])) {
        $classes = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'];
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'] = array_merge(
            ['tx_container-delete' => \B13\Container\Hooks\Datahandler\DeleteHook::class],
            $classes
        );
    }
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_container-before-start'] =
        \B13\Container\Hooks\Datahandler\DatamapBeforeStartHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_container-after-database-operations'] =
        \B13\Container\Hooks\Datahandler\DatamapAfterDatabaseOperationHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_container-after-finish'] =
        \B13\Container\Hooks\Datahandler\CommandMapAfterFinishHook::class;

    // EXT:content_defender
    $packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Package\PackageManager::class);
    if ($packageManager->isPackageActive('content_defender')) {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['tx_container-content-defender'] =
            \B13\Container\ContentDefender\Hooks\WizardItems::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][ \B13\Container\ContentDefender\Form\FormDataProvider\TcaCTypeItems::class] = [
            'depends' => [
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class
            ]
        ];

        // must be after tx_container
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_container-content-defender'] =
            \B13\Container\ContentDefender\Hooks\CommandMapHook::class;

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_container-content-defender'] =
            \B13\Container\ContentDefender\Hooks\DatamapHook::class;
    }
});
