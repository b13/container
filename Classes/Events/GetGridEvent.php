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

class GetGridEvent
{
    protected array $grid;

    public function __construct(array $grid)
    {
        $this->grid = $grid;
    }

    public function getGrid(): array
    {
        return $this->grid;
    }

    public function setGrid(array $grid): void
    {
        $this->grid = $grid;
    }
}
