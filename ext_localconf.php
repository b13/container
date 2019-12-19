<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
    \TYPO3\CMS\Backend\Utility\BackendUtility::class,
    'getPagesTSconfigPreInclude',
    B13\Container\Tca\Registry::class,
    'addPageTS'
);


if (TYPO3_MODE === 'BE') {

    // not used
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['tx_container'] =
        \B13\Container\Hooks\DrawItem::class;

    // register icons
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['tx_container'] =
        \B13\Container\Hooks\TableConfigurationPostProcessing::class;

    // remove container colPos from "unused" page-elements
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used']['tx_container'] =
        \B13\Container\Hooks\UsedRecords::class . '->addContainerChilds';

    // add tx_container_parent parameter to urls
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['tx_container'] =
        \B13\Container\Hooks\WizardItems::class;

    // resolve <containerUid>-<colPos>
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_container'] =
        \B13\Container\Hooks\Datahandler::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_container'] =
        \B13\Container\Hooks\Datahandler::class;

    // Xclass LocalizationController: adds grid columns to pageColumns to translate
    // not used
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class] = [
        'className' => \B13\Container\Xclass\LocalizationController::class
    ];

}
