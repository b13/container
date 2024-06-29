<?php

declare(strict_types=1);

namespace B13\Container\Domain\Service;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Model\Container;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\SingletonInterface;

class ConfigurationService
{
    protected EventDispatcher $eventDispatcher;

    /**
     * @var array<ContainerConfiguration>
     */
    protected $containerConfigurations;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->containerConfigurations = $this->getConfigurations();

    }

    /**
     * @return array<ContainerConfiguration>
     */
    protected function getConfigurations(): array
    {
        $configurations = [];
        foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] ?? [] as $cType => $conf) {
            $configuration = new ContainerConfiguration($cType, $conf['label'], $conf['description'], $conf['grid']);
            $configuration->applyTca($conf);
            $configurations[$cType] = $configuration;
        }
        return $configurations;
    }
}
