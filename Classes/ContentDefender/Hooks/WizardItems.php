<?php

namespace B13\Container\ContentDefender\Hooks;

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

class WizardItems extends WizardItemsHook
{

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * UsedRecords constructor.
     * @param ContainerFactory|null $containerFactory
     * @param Registry|null $tcaRegistry
     */
    public function __construct(ContainerFactory $containerFactory = null, Registry $tcaRegistry = null)
    {
        if ($containerFactory === null) {
            $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        }
        if ($tcaRegistry === null) {
            $tcaRegistry = GeneralUtility::makeInstance(Registry::class);
        }
        $this->containerFactory = $containerFactory;
        $this->tcaRegistry = $tcaRegistry;
    }

    /**
     * @param array $wizardItems
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject
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
                $wizardItems = $this->removeEmptyTabs($wizardItems);
            } catch (Exception $e) {
                // not a container
            }
        }
    }

    /**
     * @param array $wizardItems
     * @param string $field
     * @param array $values
     * @param bool $allowed
     * @return array
     */
    protected function removeDisallowedValues(array $wizardItems, $field, array $values, $allowed = true)
    {
        foreach ($wizardItems as $key => $configuration) {
            $keyParts = explode('_', $key, 2);
            if (count($keyParts) === 1 || !isset($configuration['tt_content_defValues'][$field])) {
                continue;
            }

            if (($allowed && !in_array($configuration['tt_content_defValues'][$field], $values))
                || (!$allowed && in_array($configuration['tt_content_defValues'][$field], $values))
            ) {
                unset($wizardItems[$key]);
                continue;
            }
        }

        return $wizardItems;
    }

    /**
     * @param array $wizardItems
     * @return array
     */
    protected function removeEmptyTabs(array $wizardItems)
    {
        $availableWizardItems = [];
        foreach ($wizardItems as $key => $def) {
            $keyParts = explode('_', $key, 2);
            if (count($keyParts) === 1) {
                continue;
            }
            $availableWizardItems[$keyParts[0]] = $key;
            $availableWizardItems[$key] = $key;
        }

        return array_intersect_key($wizardItems, $availableWizardItems);
    }
}
