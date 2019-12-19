<?php

namespace  B13\Container\Hooks;

use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WizardItems implements NewContentElementWizardHookInterface
{

    public function manipulateWizardItems(&$wizardItems, &$parentObject)
    {
        $parent = (int)GeneralUtility::_GP('tx_container_parent');
        if ($parent > 0) {
            foreach ($wizardItems as $key => $wizardItem) {
                $wizardItems[$key]['tt_content_defValues']['tx_container_parent'] = $parent;
                $wizardItems[$key]['params'] .= '&defVals[tt_content][tx_container_parent]=' . $parent;

            }
        }
    }


}
