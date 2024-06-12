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

class BeforeContainerPreviewIsRendered
{
    protected Container $container;

    protected array $viewVariables = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getViewVariables(): array
    {
        return $this->viewVariables;
    }

    public function setViewVariables(array $viewVariables): void
    {
        $this->viewVariables = $viewVariables;
    }
}
