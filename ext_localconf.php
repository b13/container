<?php

defined('TYPO3') || die('Access denied.');

call_user_func(static function () {
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
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['content_defender']['ColumnConfigurationManipulationHook']['tx_container'] =
            \B13\Container\ContentDefender\Hooks\ColumnConfigurationManipulationHook::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\IchHabRecht\ContentDefender\Hooks\DatamapDataHandlerHook::class] = [
            'className' => \B13\Container\ContentDefender\Xclasses\DatamapHook::class,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\IchHabRecht\ContentDefender\Hooks\CmdmapDataHandlerHook::class] = [
            'className' => \B13\Container\ContentDefender\Xclasses\CommandMapHook::class,
        ];
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
