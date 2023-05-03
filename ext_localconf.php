<?php

defined('TYPO3') || die('Access denied.');

call_user_func(static function () {
    $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);

    if ($typo3Version->getMajorVersion() === 10) {
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
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['tx_container'] =
            \B13\Container\Hooks\TableConfigurationPostProcessing::class;
    }

    if ($typo3Version->getMajorVersion() < 12) {
        // remove container colPos from "unused" page-elements (v12: IsContentUsedOnPageLayoutEvent)
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used']['tx_container'] =
            \B13\Container\Hooks\UsedRecords::class . '->addContainerChildren';
        // add tx_container_parent parameter to wizard items (v12: ModifyNewContentElementWizardItemsEvent)
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['tx_container'] =
            \B13\Container\Hooks\WizardItems::class;
        // LocalizationController Xclass (v12: AfterRecordSummaryForLocalizationEvent)
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class] = [
            'className' => \B13\Container\Xclasses\LocalizationController::class,
        ];
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\Features::class)->isFeatureEnabled('fluidBasedPageModule') === false) {
            // draw container grid
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] =
                \B13\Container\Hooks\DrawItem::class;
            // else, if enabled we register container previewRenderer in registry foreach container CType
        }
    }

    $commandMapHooks = [
        'tx_container-post-process' => \B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class,
        'tx_container-before-start' => \B13\Container\Hooks\Datahandler\CommandMapBeforeStartHook::class,
        'tx_container-delete' => \B13\Container\Hooks\Datahandler\DeleteHook::class,
        'tx_container-after-finish' => \B13\Container\Hooks\Datahandler\CommandMapAfterFinishHook::class,
    ];

    $datamapHooks = [
        'tx_container-before-start' => \B13\Container\Hooks\Datahandler\DatamapBeforeStartHook::class,
        'tx_container-pre-process-field-array' => \B13\Container\Hooks\Datahandler\DatamapPreProcessFieldArrayHook::class,
    ];

    // EXT:content_defender
    $packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Package\PackageManager::class);
    if ($packageManager->isPackageActive('content_defender')) {
        $contentDefenderVersion = $packageManager->getPackage('content_defender')->getPackageMetaData()->getVersion();
        if (version_compare($contentDefenderVersion, '3.1.0', '>=') || $contentDefenderVersion === 'dev-main') {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['content_defender']['ColumnConfigurationManipulationHook']['tx_container'] =
                \B13\Container\ContentDefender\Hooks\ColumnConfigurationManipulationHook::class;
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\IchHabRecht\ContentDefender\Hooks\DatamapDataHandlerHook::class] = [
                'className' => \B13\Container\ContentDefender\Xclasses\DatamapHook::class,
            ];
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\IchHabRecht\ContentDefender\Hooks\CmdmapDataHandlerHook::class] = [
                'className' => \B13\Container\ContentDefender\Xclasses\CommandMapHook::class,
            ];
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

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][B13\Container\Updates\ContainerMigrateSorting::IDENTIFIER]
        = B13\Container\Updates\ContainerMigrateSorting::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][B13\Container\Updates\ContainerDeleteChildrenWithWrongPid::IDENTIFIER]
        = B13\Container\Updates\ContainerDeleteChildrenWithWrongPid::class;
});
