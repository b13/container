<?php

namespace B13\Container\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;

class Registry implements SingletonInterface
{

    /**
     * @param string $cType
     * @param string $label
     * @param string $description
     * @param array $grid
     * @param string $icon
     * @param string $backendTemplate
     * @param string $gridTemplate
     * @param bool $registerInNewContentElementWizard
     */
    public function addContainer(
        string $cType,
        string $label,
        string $description,
        array $grid,
        string $icon = 'EXT:container/Resources/Public/Icons/Extension.svg',
        string $backendTemplate = 'EXT:container/Resources/Private/Templates/Container.html',
        string $gridTemplate = 'EXT:container/Resources/Private/Templates/Grid.html',
        bool $registerInNewContentElementWizard = true
    ): void
    {

        ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [$label, $cType, $cType]
        );

        foreach ($grid as $row) {
            foreach ($row as $column) {
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                    $column['name'],
                    $column['colPos']
                ];
            }
        }

        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cType] = $cType;
        $GLOBALS['TCA']['tt_content']['types'][$cType]['showitem'] = 'sys_language_uid,CType,tx_container_parent,colPos,hidden';

        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType] = [
            'cType' => $cType,
            'icon' => $icon,
            'label' => $label,
            'description' => $description,
            'backendTemplate' => $backendTemplate,
            'grid' => $grid,
            'gridTemplate' => $gridTemplate,
            'registerInNewContentElementWizard' => $registerInNewContentElementWizard
        ];
    }


    /**
     * @param string $cType
     * @param string $label
     * @param string $description
     * @param string $icon
     * @param array $grid
     * @param string $backendTemplate
     * @param string $gridTemplate
     * @param bool $registerInNewContentElementWizard
     */
    public function registerContainer(
        string $cType,
        string $label,
        string $description,
        string $icon = 'EXT:container/Resources/Public/Icons/Extension.svg',
        array $grid = [],
        string $backendTemplate = 'EXT:container/Resources/Private/Templates/Container.html',
        string $gridTemplate = 'EXT:container/Resources/Private/Templates/Grid.html',
        bool $registerInNewContentElementWizard = true
    ): void
    {
        trigger_error('use "addContainer" instead of "registerContainer", parameter order changed!', E_USER_DEPRECATED);
        $this->addContainer(
            $cType,
            $label,
            $description,
            $grid,
            $icon,
            $backendTemplate,
            $gridTemplate,
            $registerInNewContentElementWizard
        );
    }

    /**
     * @param string $cType
     * @param int $colPos
     * @return array
     */
    public function getAllowedConfiguration(string $cType, int $colPos): array
    {
        $allowed = [];
        $rows = $this->getGrid($cType);
            foreach ($rows as $columns) {
                foreach ($columns as $column) {
                    if ((int)$column['colPos'] === $colPos && is_array($column['allowed'])) {
                        $allowed = $column['allowed'];
                    }
                }
            }
        return $allowed;
    }

    /**
     * @return void
     */
    public function registerIcons(): void
    {
        if (is_array($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $containerConfiguration) {
                if (file_exists(GeneralUtility::getFileAbsFileName($containerConfiguration['icon']))) {
                    $provider = BitmapIconProvider::class;
                    if (strpos($containerConfiguration['icon'], '.svg') !== false) {
                        $provider = SvgIconProvider::class;
                    }
                    $iconRegistry->registerIcon(
                        $containerConfiguration['cType'],
                        $provider,
                        ['source' => $containerConfiguration['icon']]
                    );
                } else {
                    try {
                        $existingIconConfiguration = $iconRegistry->getIconConfigurationByIdentifier($containerConfiguration['icon']);
                        $iconRegistry->registerIcon(
                            $containerConfiguration['cType'],
                            $existingIconConfiguration['provider'],
                            $existingIconConfiguration['options']
                        );
                    } catch (\TYPO3\CMS\Core\Exception $e) {

                    }
                }
            }
        }
    }

    /**
     * @param string $cType
     * @return bool
     */
    public function isContainerElement(string $cType): bool
    {
        return !empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]);
    }

    /**
     * @return array
     */
    public function getRegisteredCTypes(): array
    {
        return array_keys((array)$GLOBALS['TCA']['tt_content']['containerConfiguration']);
    }

    /**
     * @param string $cType
     * @return array
     */
    public function getGrid(string $cType): array
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'])) {
            return [];
        }
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'];
    }

    /**
     * @param string $cType
     * @return string|null
     */
    public function getGridTemplate(string $cType): ?string
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridTemplate'])) {
            return null;
        }
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridTemplate'];
    }

    /**
     * @param string $cType
     * @return array
     * @deprecated
     */
    public function getAvaiableColumns(string $cType): array
    {
        trigger_error('use "getAvailableColumns" instead of "getAvaiableColumns"', E_USER_DEPRECATED);
        return $this->getAvailableColumns($cType);
    }

    /**
     * @param string $cType
     * @param int $colPos
     * @return string|null
     */
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

    /**
     * @param string $cType
     * @return array
     */
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

    /**
     * @return array
     */
    public function getAllAvailableColumns(): array
    {
        $columns = [];
        foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $containerConfiguration) {
            $grid = $containerConfiguration['grid'];
            foreach ($grid as $row) {
                foreach ($row as $column) {
                    $columns[] = $column;
                }
            }
        }
        return $columns;
    }

    /**
     * Adds TSconfig
     *
     * @param array $TSdataArray
     * @param int $id
     * @param array $rootLine
     * @param array $returnPartArray
     * @return array
     */
    public function addPageTS($TSdataArray, $id, $rootLine, $returnPartArray): array
    {
        if (!empty($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            $content = '
mod.wizards.newContentElement.wizardItems.container.header = Container
mod.wizards.newContentElement.wizardItems.container.show = *
';
            foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $cType => $containerConfiguration) {
                if ($containerConfiguration['registerInNewContentElementWizard'] === true) {
                    $content .= 'mod.wizards.newContentElement.wizardItems.container.elements {
    ' . $cType . ' {
        title = ' . $containerConfiguration['label'] . '
        description = ' . $containerConfiguration['description'] . '
        iconIdentifier = ' . $cType . '
        tt_content_defValues.CType = ' . $cType . '
    }
}
';
                }
                $content .= 'mod.web_layout.tt_content.preview {
    ' . $cType . ' = ' . $containerConfiguration['backendTemplate'] . '
}
';
            }
            $TSdataArray['default'] .= LF . $content;
        }
        return [$TSdataArray, $id, $rootLine, $returnPartArray];
    }
}
