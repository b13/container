<?php

declare(strict_types=1);

namespace B13\Container\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\SingletonInterface;

class DatahandlerProcess implements SingletonInterface
{
    protected $containerInProcess = [];

    public function isContainerInProcess(int $containerId): bool
    {
        return in_array($containerId, $this->containerInProcess, true);
    }

    public function startContainerProcess(int $containerId): void
    {
        if (!in_array($containerId, $this->containerInProcess, true)) {
            $this->containerInProcess[] = $containerId;
        }
    }

    public function endContainerProcess(int $containerId): void
    {
        if (in_array($containerId, $this->containerInProcess, true)) {
            $this->containerInProcess = array_diff([$containerId], $this->containerInProcess);
        }
    }
}
