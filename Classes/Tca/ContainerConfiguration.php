<?php

declare(strict_types=1);

namespace B13\Container\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class ContainerConfiguration
{
    protected string $cType = '';
    protected string $label = '';
    protected string $description = '';
    protected array $grid = [];
    protected string $icon = 'EXT:container/Resources/Public/Icons/Extension.svg';
    protected string $backendTemplate = 'EXT:container/Resources/Private/Templates/Container.html';
    protected string $gridTemplate = 'EXT:container/Resources/Private/Templates/Grid.html';
    protected array $gridPartialPaths = [
        'EXT:backend/Resources/Private/Partials/',
        'EXT:container/Resources/Private/Partials/',
    ];
    protected array $gridLayoutPaths = [];
    protected bool $saveAndCloseInNewContentElementWizard = true;
    protected bool $registerInNewContentElementWizard = true;
    protected string $group = 'container';
    protected string $relativeToField = '';
    protected string $relativePosition = '';
    protected array $defaultValues = [];

    public function __construct(
        string $cType,
        string $label,
        string $description,
        array $grid
    ) {
        $this->cType = $cType;
        $this->label = $label;
        $this->description = $description;
        $this->grid = $grid;
    }

    public function setIcon(string $icon): ContainerConfiguration
    {
        $this->icon = $icon;
        return $this;
    }

    public function setBackendTemplate(string $backendTemplate): ContainerConfiguration
    {
        $this->backendTemplate = $backendTemplate;
        return $this;
    }

    public function setGridTemplate(string $gridTemplate): ContainerConfiguration
    {
        $this->gridTemplate = $gridTemplate;
        return $this;
    }

    public function setGridPartialPaths(array $gridPartialPaths): ContainerConfiguration
    {
        $this->gridPartialPaths = $gridPartialPaths;
        return $this;
    }

    public function addGridPartialPath(string $gridPartialPath): ContainerConfiguration
    {
        $this->gridPartialPaths[] = $gridPartialPath;
        return $this;
    }

    public function getGridLayoutPaths(): array
    {
        return $this->gridLayoutPaths;
    }

    public function setGridLayoutPaths(array $gridLayoutPaths): ContainerConfiguration
    {
        $this->gridLayoutPaths = $gridLayoutPaths;
        return $this;
    }

    public function addGridLayoutPath(string $gridLayoutPath): ContainerConfiguration
    {
        $this->gridLayoutPaths[] = $gridLayoutPath;
        return $this;
    }

    public function setSaveAndCloseInNewContentElementWizard(bool $saveAndCloseInNewContentElementWizard): ContainerConfiguration
    {
        $this->saveAndCloseInNewContentElementWizard = $saveAndCloseInNewContentElementWizard;
        return $this;
    }

    public function setRegisterInNewContentElementWizard(bool $registerInNewContentElementWizard): ContainerConfiguration
    {
        $this->registerInNewContentElementWizard = $registerInNewContentElementWizard;
        return $this;
    }

    public function setGroup(string $group): ContainerConfiguration
    {
        $this->group = $group;
        return $this;
    }

    public function setRelativeToField(string $relativeToField): ContainerConfiguration
    {
        $this->relativeToField = $relativeToField;
        return $this;
    }

    public function setRelativePosition(string $relativePosition): ContainerConfiguration
    {
        $this->relativePosition = $relativePosition;
        return $this;
    }

    public function getCType(): string
    {
        return $this->cType;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getGrid(): array
    {
        return $this->grid;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getBackendTemplate(): string
    {
        return $this->backendTemplate;
    }

    public function isRegisterInNewContentElementWizard(): bool
    {
        return $this->registerInNewContentElementWizard;
    }

    public function getDefaultValues(): array
    {
        return $this->defaultValues;
    }

    /**
     * @return string[]
     */
    public function getGridPartialPaths(): array
    {
        return $this->gridPartialPaths;
    }

    public function getSaveAndCloseInNewContentElementWizard(): bool
    {
        return $this->saveAndCloseInNewContentElementWizard;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getRelativeToField(): string
    {
        return $this->relativeToField;
    }

    public function getRelativePosition(): string
    {
        return $this->relativePosition;
    }

    public function setDefaultValues(array $defaultValues): ContainerConfiguration
    {
        $this->defaultValues = $defaultValues;
        return $this;
    }

    protected function setLabel(string $label): ContainerConfiguration
    {
        $this->label = $label;
        return $this;
    }

    public function setDescription(string $description): ContainerConfiguration
    {
        $this->description = $description;
        return $this;
    }

    public function getGridTemplate(): string
    {
        return $this->gridTemplate;
    }

    public function changeGridColumnConfiguration(int $colPos, array $override): void
    {
        $rows = $this->getGrid();
        $modRows = [];
        $columnConfigurationFields = ['name', 'allowed', 'disallowed', 'maxitems', 'colspan'];
        foreach ($rows as &$columns) {
            foreach ($columns as &$column) {
                if ((int)$column['colPos'] === $colPos) {
                    foreach ($columnConfigurationFields as $field) {
                        if (isset($override[$field])) {
                            $column[$field] = $override[$field];
                        }
                    }
                }
            }
            $modRows[] = $columns;
        }
        $this->grid = $modRows;
    }

    public function toArray(): array
    {
        return [
            'cType' => $this->cType,
            'icon' => $this->icon,
            'label' => $this->label,
            'description' => $this->description,
            'backendTemplate' => $this->backendTemplate,
            'grid' => $this->grid,
            'gridTemplate' => $this->gridTemplate,
            'gridPartialPaths' => $this->gridPartialPaths,
            'gridLayoutPaths' => $this->gridLayoutPaths,
            'saveAndCloseInNewContentElementWizard' => $this->saveAndCloseInNewContentElementWizard,
            'registerInNewContentElementWizard' => $this->registerInNewContentElementWizard,
            'group' => $this->group,
            'defaultValues' => $this->defaultValues,
        ];
    }
}
