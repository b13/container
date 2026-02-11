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

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\Grid;
use TYPO3\CMS\Core\View\ViewInterface;

final class BeforeContainerPreviewIsRenderedEvent
{
    public function __construct(protected Container $container, protected ViewInterface $view, protected Grid $grid)
    {
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getGrid(): Grid
    {
        return $this->grid;
    }
}
