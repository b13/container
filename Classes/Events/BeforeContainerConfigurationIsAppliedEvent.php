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

use B13\Container\Tca\ContainerConfiguration;

final class BeforeContainerConfigurationIsAppliedEvent
{
    protected bool $skip = false;

    public function __construct(protected ContainerConfiguration $containerConfiguration)
    {
    }

    public function skip(): void
    {
        $this->skip = true;
    }

    public function shouldBeSkipped(): bool
    {
        return $this->skip;
    }

    public function getContainerConfiguration(): ContainerConfiguration
    {
        return $this->containerConfiguration;
    }
}
