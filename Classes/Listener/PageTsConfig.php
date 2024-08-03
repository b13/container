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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;
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
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            return;
        }
        $tsConfig = $event->getTsConfig();
        $tsConfig = array_merge(['pagesTsConfig-package-container' => $this->tcaRegistry->getPageTsString()], $tsConfig);
        $event->setTsConfig($tsConfig);
    }
}
