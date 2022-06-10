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
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;

class BootCompleted
{
    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(Registry $tcaRegistry)
    {
        $this->tcaRegistry = $tcaRegistry;
    }

    public function __invoke(BootCompletedEvent $event): void
    {
        $this->tcaRegistry->registerIcons();
    }
}
