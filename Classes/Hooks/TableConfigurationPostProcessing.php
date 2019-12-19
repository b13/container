<?php

namespace B13\Container\Hooks;


use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TableConfigurationPostProcessing implements TableConfigurationPostProcessingHookInterface
{

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    public function __construct(Registry $tcaRegistry = null)
    {
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    public function processData()
    {
        $this->tcaRegistry->registerIcons();
    }
}
