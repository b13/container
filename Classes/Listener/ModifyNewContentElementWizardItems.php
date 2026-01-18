<?php

declare(strict_types=1);

namespace B13\Container\Listener;

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
use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ModifyNewContentElementWizardItems
{
    public function __construct(protected ContainerFactory $containerFactory, protected Registry $tcaRegistry)
    {
    }

    protected function applyRestrictions(int $parent, int $colPos, array $wizardItems): array
    {
        return $wizardItems;
        // DebuggerUtility::var_dump($wizardItems);
        try {
            $container = $this->containerFactory->buildContainer($parent);
        } catch (Exception $e) {
            // not a container
            return $wizardItems;
        }
        $cType = $container->getCType();
        $columnConfiguration = $this->tcaRegistry->getContentDefenderConfiguration($cType, $colPos);

        $allowedConfiguration = $columnConfiguration['allowed.'] ?? [];
        foreach ($allowedConfiguration as $field => $value) {
            $allowedValues = GeneralUtility::trimExplode(',', $value);
            $wizardItems = $this->removeDisallowedValues($wizardItems, $field, $allowedValues);
        }

        $disallowedConfiguration = $columnConfiguration['disallowed.'] ?? [];
        foreach ($disallowedConfiguration as $field => $value) {
            $disAllowedValues = GeneralUtility::trimExplode(',', $value);
            $wizardItems = $this->removeDisallowedValues($wizardItems, $field, $disAllowedValues, false);
        }

        $availableWizardItems = [];
        foreach ($wizardItems as $key => $_) {
            $keyParts = explode('_', $key, 2);
            if (count($keyParts) === 1) {
                continue;
            }
            $availableWizardItems[$keyParts[0]] = $key;
            $availableWizardItems[$key] = $key;
        }

        $wizardItems = array_intersect_key($wizardItems, $availableWizardItems);
        return $wizardItems;
    }

    public function __invoke(ModifyNewContentElementWizardItemsEvent $event): void
    {
        $parent = $this->getParentIdFromRequest();
        if ($parent !== null) {
            $typo3Version = (GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion();
            $wizardItems = $this->applyRestrictions($parent, $event->getColPos(), $event->getWizardItems());
            foreach ($wizardItems as $key => $wizardItem) {
                if ($typo3Version < 13) {
                    $wizardItems[$key]['tt_content_defValues']['tx_container_parent'] = $parent;
                    if (!isset($wizardItems[$key]['params'])) {
                        $wizardItems[$key]['params'] = '?defVals[tt_content][tx_container_parent]=' . $parent;
                    } else {
                        $wizardItems[$key]['params'] .= '&defVals[tt_content][tx_container_parent]=' . $parent;
                    }
                } else {
                    $wizardItems[$key]['defaultValues']['tx_container_parent'] = $parent;
                }
            }
            $event->setWizardItems($wizardItems);
        }
    }

    protected function removeDisallowedValues(array $wizardItems, $field, array $values, $allowed = true)
    {
        foreach ($wizardItems as $key => $configuration) {
            $keyParts = explode('_', $key, 2);
            if (count($keyParts) === 1 || (!isset($configuration['defaultValues'][$field]) && !isset($configuration['tt_content_defValues'][$field]))) {
                continue;
            }

            $defaultValue = $configuration['defaultValues'][$field] ?? $configuration['tt_content_defValues'][$field] ?? '';

            if (($allowed && !in_array($defaultValue, $values))
                || (!$allowed && in_array($defaultValue, $values))
            ) {
                unset($wizardItems[$key]);
                continue;
            }
        }

        return $wizardItems;
    }

    protected function getParentIdFromRequest(): ?int
    {
        $request = $this->getServerRequest();
        if ($request === null) {
            return null;
        }
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['tx_container_parent']) && (int)$queryParams['tx_container_parent'] > 0) {
            return (int)$queryParams['tx_container_parent'];
        }
        return null;
    }

    protected function getServerRequest(): ?ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
