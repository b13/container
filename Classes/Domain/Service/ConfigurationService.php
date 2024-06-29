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

use B13\Container\Tca\ContainerConfiguration;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;

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

    public function getContentDefenderConfiguration(string $cType, int $colPos): array
    {
        $contentDefenderConfiguration = [];
        $rows = $this->getGrid($cType);
        foreach ($rows as $columns) {
            foreach ($columns as $column) {
                if ((int)$column['colPos'] === $colPos) {
                    $contentDefenderConfiguration['allowed.'] = $column['allowed'] ?? [];
                    $contentDefenderConfiguration['disallowed.'] = $column['disallowed'] ?? [];
                    $contentDefenderConfiguration['maxitems'] = $column['maxitems'] ?? 0;
                }
            }
        }
        return $contentDefenderConfiguration;
    }

    public function getAllAvailableColumnsColPos(string $cType): array
    {
        $columns = $this->getAvailableColumns($cType);
        $availableColumnsColPos = [];
        foreach ($columns as $column) {
            $availableColumnsColPos[] = $column['colPos'];
        }
        return $availableColumnsColPos;
    }

    public function isContainerElement(string $cType): bool
    {
        return !empty($this->containerConfigurations[$cType]);
    }

    public function getRegisteredCTypes(): array
    {
        return array_keys($this->containerConfigurations);
    }

    public function getGrid(string $cType): array
    {
        if (!isset($this->containerConfigurations[$cType])) {
            return [];
        }
        $containerConfiguration = $this->containerConfigurations[$cType];
        return $containerConfiguration->getGrid();
    }

    public function getGridTemplate(string $cType): ?string
    {
        if (!isset($this->containerConfigurations[$cType])) {
            return null;
        }
        $containerConfiguration = $this->containerConfigurations[$cType];
        return $containerConfiguration->getGridTemplate();
    }

    public function getGridPartialPaths(string $cType): array
    {
        if (!isset($this->containerConfigurations[$cType])) {
            return [];
        }
        $containerConfiguration = $this->containerConfigurations[$cType];
        return $containerConfiguration->getGridPartialPaths();
    }

    public function getGridLayoutPaths(string $cType): array
    {
        if (!isset($this->containerConfigurations[$cType])) {
            return [];
        }
        $containerConfiguration = $this->containerConfigurations[$cType];
        return $containerConfiguration->getGridLayoutPaths();
    }

    public function getColPosName(string $cType, int $colPos): ?string
    {
        $grid = $this->getGrid($cType);
        foreach ($grid as $row) {
            foreach ($row as $column) {
                if ($column['colPos'] === $colPos) {
                    return (string)$column['name'];
                }
            }
        }
        return null;
    }

    public function getAvailableColumns(string $cType): array
    {
        $columns = [];
        $grid = $this->getGrid($cType);
        foreach ($grid as $row) {
            foreach ($row as $column) {
                $columns[] = $column;
            }
        }
        return $columns;
    }

    public function getAllAvailableColumns(): array
    {
        $columns = [];
        foreach ($this->containerConfigurations as $containerConfiguration) {
            $grid = $containerConfiguration->getGrid();
            foreach ($grid as $row) {
                foreach ($row as $column) {
                    $columns[] = $column;
                }
            }
        }
        return array_unique($columns);
    }
}
