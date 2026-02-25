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
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Imaging\IconRegistry;

#[AsEventListener(identifier: 'tx-container-boot-completed')]
class BootCompleted
{
    public function __construct(protected Registry $tcaRegistry, protected IconRegistry $iconRegistry)
    {
    }

    public function __invoke(BootCompletedEvent $event): void
    {
        $this->tcaRegistry->registerIcons($this->iconRegistry);
    }
}
