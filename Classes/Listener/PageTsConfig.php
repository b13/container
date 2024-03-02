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

use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Configuration\Event\ModifyLoadedPageTsConfigEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTsConfig
{
    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(Registry $tcaRegistry)
    {
        $this->tcaRegistry = $tcaRegistry;
    }

    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        // s. https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/ContentElements/CustomBackendPreview.html#ConfigureCE-Preview-EventListener
        // s. https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-102834-RemoveItemsFromNewContentElementWizard.html
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 12) {
            return;
        }
        $tsConfig = $event->getTsConfig();
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 11) {
            $tsConfig = array_merge(['pageTsConfig-package-container' => $this->tcaRegistry->getPageTsString()], $tsConfig);
        } else {
            $tsConfig['default'] = trim($this->tcaRegistry->getPageTsString() . "\n" . ($tsConfig['default'] ?? ''));
        }
        $event->setTsConfig($tsConfig);
    }
}
