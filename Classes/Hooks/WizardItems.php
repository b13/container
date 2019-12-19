<?php

namespace  B13\Container\Hooks;


use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WizardItems implements NewContentElementWizardHookInterface
{

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    public function __construct(Registry $tcaRegistry = null)
    {
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    public function manipulateWizardItems(&$wizardItems, &$parentObject)
    {
        $this->tcaRegistry->registerIcons();
        $parent = (int)GeneralUtility::_GP('tx_container_parent');
        if ($parent > 0) {
            foreach ($wizardItems as $key => $wizardItem) {
                $wizardItems[$key]['tt_content_defValues']['tx_container_parent'] = $parent;
                $wizardItems[$key]['params'] .= '&defVals[tt_content][tx_container_parent]=' . $parent;

            }
        }
    }


}
