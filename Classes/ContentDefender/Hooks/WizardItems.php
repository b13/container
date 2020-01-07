<?php

namespace  B13\Container\ContentDefender\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Tca\Registry;
use IchHabRecht\ContentDefender\Hooks\WizardItemsHook;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class WizardItems extends WizardItemsHook
{

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory = null;

    /**
     * UsedRecords constructor.
     * @param ContainerFactory|null $containerFactory
     * @param Registry|null $tcaRegistry
     */
    public function __construct(ContainerFactory $containerFactory = null, Registry $tcaRegistry = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @param array $wizardItems
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject
     * @return void
     */
    public function manipulateWizardItems(&$wizardItems, &$parentObject)
    {
        $parent = (int)GeneralUtility::_GP('tx_container_parent');
        $colPos = (int)GeneralUtility::_GP('colPos');
        if ($parent > 0 && $colPos > 0) {
            try {
                $container = $this->containerFactory->buildContainer($parent);
                $cType = $container->getCType();
                $allowedConfiguration = $this->tcaRegistry->getAllowedConfiguration($cType, $colPos);
                foreach ($allowedConfiguration as $field => $value) {
                    $allowedValues = GeneralUtility::trimExplode(',', $value);
                    $wizardItems = $this->removeDisallowedValues($wizardItems, $field, $allowedValues);
                }
            } catch (Exception $e) {
                // not a container
            }
        }
    }


}
