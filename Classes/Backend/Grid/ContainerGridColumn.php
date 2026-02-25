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

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn;
use TYPO3\CMS\Backend\View\PageLayoutContext;

class ContainerGridColumn extends GridColumn
{
    public const CONTAINER_COL_POS_DELIMITER = '-';

    public function __construct(
        PageLayoutContext $context,
        array $columnDefinition,
        protected Container $container,
        protected ?string $newContentUrl,
        protected bool $skipNewContentElementWizard
    ) {
        parent::__construct($context, $columnDefinition);
    }

    public function getContainerUid(): int
    {
        return $this->container->getUidOfLiveWorkspace();
    }

    public function getNewContentElementWizardShouldBeSkipped(): bool
    {
        return $this->skipNewContentElementWizard;
    }

    public function getTitle(): string
    {
        return (string)$this->getLanguageService()->sL($this->getColumnName());
    }

    public function getAllowNewContent(): bool
    {
        if ($this->container->getLanguage() > 0 && $this->container->isConnectedMode()) {
            return false;
        }
        return $this->newContentUrl !== null;
    }

    public function isActive(): bool
    {
        // yes we are active
        return true;
    }

    public function getNewContentUrl(): string
    {
        if ($this->newContentUrl === null) {
            return '';
        }
        return $this->newContentUrl;
    }
}
