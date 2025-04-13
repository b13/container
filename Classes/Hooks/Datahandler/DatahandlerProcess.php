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
    protected array $commands = [];
    protected bool $pageIsCopied = false;

    public function startPageCopy(): void
    {
        $this->pageIsCopied = true;
    }

    public function stopPageCopy(): void
    {
        $this->pageIsCopied = false;
    }

    public function isPageCopied(): bool
    {
        return $this->pageIsCopied;
    }

    public function startCommand(int $id, array $command): void
    {
        if (!isset($command['copy'])) {
           # return;
        }
        if (isset($this->commands[$id])) {
            throw new \RuntimeException('already started');
        }
        $this->commands[$id] = $command;
    }

    public function stopCommand(int $id): void
    {
        if (!isset($this->commands[$id])) {
            return;
            throw new \RuntimeException('not started');
        }
        unset($this->commands[$id]);
    }

    public function getCommand(int $id): ?array
    {
        return $this->commands[$id] ?? null;
    }

    public function hasCommand(int $id): bool
    {
        return isset($this->commands[$id]);
    }

    public function isRunning(): bool
    {
        return !empty($this->commands);
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

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
            $this->containerInProcess = array_diff($this->containerInProcess, [$containerId]);
        }
    }
}
