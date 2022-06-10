<?php

declare(strict_types=1);

namespace B13\Container\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Http\ServerRequest;

class WizardItems implements NewContentElementWizardHookInterface
{
    public function manipulateWizardItems(&$wizardItems, &$parentObject)
    {
        $parent = $this->getParentIdFromRequest();
        if ($parent !== null) {
            foreach ($wizardItems as $key => $wizardItem) {
                $wizardItems[$key]['tt_content_defValues']['tx_container_parent'] = $parent;
                if (!isset($wizardItems[$key]['params'])) {
                    $wizardItems[$key]['params'] = '?defVals[tt_content][tx_container_parent]=' . $parent;
                } else {
                    $wizardItems[$key]['params'] .= '&defVals[tt_content][tx_container_parent]=' . $parent;
                }
            }
        }
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
