<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(static function () {
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
        \TYPO3\CMS\Backend\Utility\BackendUtility::class,
        'getPagesTSconfigPreInclude',
        B13\Container\Tca\Registry::class,
        'addPageTS'
    );

    // register default icons
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconsToRegister = [
        'container-1col',
        'container-2col',
        'container-2col-left',
        'container-2col-right',
        'container-3col',
        'container-4col',
    ];
    foreach ($iconsToRegister as $icon) {
        $iconRegistry->registerIcon(
            $icon,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => 'EXT:container/Resources/Public/Icons/' . $icon . '.svg',
            ]
        );
    }

    // LocalizationController Xclass
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class] = [
        'className' => \B13\Container\Xclasses\LocalizationController::class
    ];

    if (false === \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\Features::class)->isFeatureEnabled('fluidBasedPageModule')) {
        // draw container grid
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] =
            \B13\Container\Hooks\DrawItem::class;
    }
    // else, if enabled we register container previewRenderer in registry foreach container CType

    // register icons
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['tx_container'] =
        \B13\Container\Hooks\TableConfigurationPostProcessing::class;

    // remove container colPos from "unused" page-elements
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used']['tx_container'] =
        \B13\Container\Hooks\UsedRecords::class . '->addContainerChildren';

    // add tx_container_parent parameter to wizard items
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['tx_container'] =
        \B13\Container\Hooks\WizardItems::class;

    $commandMapHooks = [
        'tx_container-post-process' => \B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class,
        'tx_container-before-start' => \B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook::class,
        'tx_container-delete' => \B13\Container\Hooks\Datahandler\DeleteHook::class,
        'tx_container-after-finish' => \B13\Container\Hooks\Datahandler\CommandMapAfterFinishHook::class
    ];

    $datamapHooks = [
        'tx_container-before-start' => \B13\Container\Hooks\Datahandler\DatamapBeforeStartHook::class
    ];

    // EXT:content_defender
    $packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Package\PackageManager::class);
    if ($packageManager->isPackageActive('content_defender')) {
        if (version_compare($packageManager->getPackage('content_defender')->getPackageMetaData()->getVersion(), '3.1.0', '<')) {
            trigger_error('update EXT:content_defender to 3.1', E_USER_DEPRECATED);
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['tx_container-content-defender'] =
                \B13\Container\ContentDefender\Hooks\WizardItems::class;
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][ \B13\Container\ContentDefender\Form\FormDataProvider\TcaCTypeItems::class] = [
                'depends' => [
                    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class
                ]
            ];
            $commandMapHooks['tx_container-content-defender'] = \B13\Container\ContentDefender\Hooks\CommandMapHook::class;
            $datamapHooks['tx_container-content-defender'] = \B13\Container\ContentDefender\Hooks\DatamapHook::class;
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['content_defender']['ColumnConfigurationManipulationHook']['tx_container'] =
                \B13\Container\Hooks\ContentDefender\ColumnConfigurationManipulationHook::class;
            $commandMapHooks['tx_container-content-defender'] = \B13\Container\Hooks\ContentDefender\CommandMapHook::class;
            $datamapHooks['tx_container-content-defender'] = \B13\Container\Hooks\ContentDefender\DatamapHook::class;
        }
    }

    // set our hooks at the beginning of Datamap Hooks
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'] = array_merge(
        $commandMapHooks,
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'] ?? []
    );
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] = array_merge(
        $datamapHooks,
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']
    );
});
