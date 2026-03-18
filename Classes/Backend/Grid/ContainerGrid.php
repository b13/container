<?php

declare(strict_types=1);

namespace B13\Container\Backend\Grid;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Backend\View\BackendLayout\Grid\Grid;

class ContainerGrid extends Grid
{
    public function getSpan(): int
    {
        if (!isset($this->rows[0])) {
            return 1;
        }
        $span = 0;
        foreach ($this->rows[0]->getColumns() as $column) {
            $span += $column->getColSpan();
        }
        return $span ?: 1;
    }
}
