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
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

#[AsEventListener(identifier: 'tx-container-page-ts-config')]
class PageTsConfig
{
    public function __construct(protected Registry $tcaRegistry)
    {
    }

    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        $tsConfig = $event->getTsConfig();
        $tsConfig = array_merge(['pagesTsConfig-package-container' => $this->tcaRegistry->getPageTsString()], $tsConfig);
        $event->setTsConfig($tsConfig);
    }
}
