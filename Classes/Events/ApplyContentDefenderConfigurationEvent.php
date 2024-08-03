<?php

declare(strict_types=1);

namespace B13\Container\Events;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class ApplyContentDefenderConfigurationEvent
{
    protected array $allowed;
    protected array $disallowed;
    protected int $maxItems;
    protected string $cType;
    protected int $colPos;

    public function __construct(string $cType, int $colPos, array $allowed, array $disallowed, int $maxItems)
    {
        $this->cType = $cType;
        $this->colPos = $colPos;
        $this->allowed = $allowed;
        $this->disallowed = $disallowed;
        $this->maxItems = $maxItems;
    }

    public function getAllowed(): array
    {
        return $this->allowed;
    }

    public function setAllowed(array $allowed): void
    {
        $this->allowed = $allowed;
    }

    public function getDisallowed(): array
    {
        return $this->disallowed;
    }

    public function setDisallowed(array $disallowed): void
    {
        $this->disallowed = $disallowed;
    }

    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    public function getCType(): string
    {
        return $this->cType;
    }

    public function getColPos(): int
    {
        return $this->colPos;
    }
}
