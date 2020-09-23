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
     * @var bool
     */
    protected $saveAndCloseInNewContentElementWizard = true;

    /**
     * @var bool
     */
    protected $registerInNewContentElementWizard = true;

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
            'saveAndCloseInNewContentElementWizard' => $this->saveAndCloseInNewContentElementWizard,
            'registerInNewContentElementWizard' => $this->registerInNewContentElementWizard
        ];
    }
}
