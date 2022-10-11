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

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerConfiguration
{
    /**
     * @var string
     */
    protected $cType = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var mixed[]
     */
    protected $grid = [];

    /**
     * @var string
     */
    protected $icon = 'EXT:container/Resources/Public/Icons/Extension.svg';

    /**
     * @var string
     */
    protected $backendTemplate = 'EXT:container/Resources/Private/Templates/Container.html';

    /**
     * @var string
     */
    protected $gridTemplate = 'EXT:container/Resources/Private/Templates/Grid.html';

    /**
     * @var array
     */
    protected $gridPartialPaths = [
        'EXT:backend/Resources/Private/Partials/',
        'EXT:container/Resources/Private/Partials/',
    ];

    protected $gridLayoutPaths = [];

    /**
     * @var bool
     */
    protected $saveAndCloseInNewContentElementWizard = true;

    /**
     * @var bool
     */
    protected $registerInNewContentElementWizard = true;

    /**
     * @var string
     */
    protected $group = 'container';

    /**
     * @var array
     */
    protected $defaultValues = [];

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
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            $this->gridPartialPaths = [
                'EXT:backend/Resources/Private/Partials/',
                'EXT:container/Resources/Private/Partials12/',
            ];
        }
    }

    /**
     * @param string $icon
     * @return ContainerConfiguration
     */
    public function setIcon(string $icon): ContainerConfiguration
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param string $backendTemplate
     * @return ContainerConfiguration
     */
    public function setBackendTemplate(string $backendTemplate): ContainerConfiguration
    {
        $this->backendTemplate = $backendTemplate;
        return $this;
    }

    /**
     * @param string $gridTemplate
     * @return ContainerConfiguration
     */
    public function setGridTemplate(string $gridTemplate): ContainerConfiguration
    {
        $this->gridTemplate = $gridTemplate;
        return $this;
    }

    /**
     * @param array $gridPartialPaths
     * @return ContainerConfiguration
     */
    public function setGridPartialPaths(array $gridPartialPaths): ContainerConfiguration
    {
        $this->gridPartialPaths = $gridPartialPaths;
        return $this;
    }

    /**
     * @param string $gridPartialPath
     * @return ContainerConfiguration
     */
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

    /**
     * @param bool $saveAndCloseInNewContentElementWizard
     * @return ContainerConfiguration
     */
    public function setSaveAndCloseInNewContentElementWizard(bool $saveAndCloseInNewContentElementWizard): ContainerConfiguration
    {
        $this->saveAndCloseInNewContentElementWizard = $saveAndCloseInNewContentElementWizard;
        return $this;
    }

    /**
     * @param bool $registerInNewContentElementWizard
     * @return ContainerConfiguration
     */
    public function setRegisterInNewContentElementWizard(bool $registerInNewContentElementWizard): ContainerConfiguration
    {
        $this->registerInNewContentElementWizard = $registerInNewContentElementWizard;
        return $this;
    }

    /**
     * @param string $group
     * @return ContainerConfiguration
     */
    public function setGroup(string $group): ContainerConfiguration
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getCType(): string
    {
        return $this->cType;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return mixed[]
     */
    public function getGrid(): array
    {
        return $this->grid;
    }

    /**
     * @return string[]
     */
    public function getGridPartialPaths(): array
    {
        return $this->gridPartialPaths;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param array $defaultValues
     * @return ContainerConfiguration
     */
    public function setDefaultValues(array $defaultValues): ContainerConfiguration
    {
        $this->defaultValues = $defaultValues;
        return $this;
    }

    /**
     * @return mixed[]
     */
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
