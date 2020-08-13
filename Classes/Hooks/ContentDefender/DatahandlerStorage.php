<?php

declare(strict_types=1);

namespace B13\Container\Hooks\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\SingletonInterface;

class DatahandlerStorage implements SingletonInterface
{
    protected $mapping = [];

    public function addMapping(int $contentId, int $containerId): void
    {
        $this->mapping[$contentId] = $containerId;
    }

    public function hasMapping(int $contentId): bool
    {
        return !empty($this->mapping[$contentId]);
    }

    public function getMapping(int $contentId): int
    {
        return $this->mapping[$contentId];
    }
}
