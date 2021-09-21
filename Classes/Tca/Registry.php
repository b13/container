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

use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Registry implements SingletonInterface
{
    /**
     * @param ContainerConfiguration $containerConfiguration
     */
    public function configureContainer(ContainerConfiguration $containerConfiguration): void
    {
        ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                $containerConfiguration->getLabel(),
                $containerConfiguration->getCType(),
                $containerConfiguration->getCType(),
                $containerConfiguration->getGroup()
            ]
        );
        if (GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('fluidBasedPageModule')) {
            $GLOBALS['TCA']['tt_content']['types'][$containerConfiguration->getCType()]['previewRenderer'] = \B13\Container\Backend\Preview\ContainerPreviewRenderer::class;
        }

        foreach ($containerConfiguration->getGrid() as $row) {
            foreach ($row as $column) {
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                    $column['name'],
                    $column['colPos']
                ];
            }
        }

        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$containerConfiguration->getCType()] = $containerConfiguration->getCType();
        $GLOBALS['TCA']['tt_content']['types'][$containerConfiguration->getCType()]['showitem'] = '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;general,
                    header;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header.ALT.div_formlabel,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                    --palette--;;frames,
                    --palette--;;appearanceLinks,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                    categories,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                    rowDescription,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
';

        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$containerConfiguration->getCType()] = $containerConfiguration->toArray();
    }

    /**
     * @param string $cType
     * @param string $label
     * @param string $description
     * @param array $grid
     * @param string $icon
     * @param string $backendTemplate
     * @param string $gridTemplate
     * @param bool $saveAndCloseInNewContentElementWizard
     * @param bool $registerInNewContentElementWizard
     * @deprecated
     */
    public function addContainer(
        string $cType,
        string $label,
        string $description,
        array $grid,
        string $icon = 'EXT:container/Resources/Public/Icons/Extension.svg',
        string $backendTemplate = 'EXT:container/Resources/Private/Templates/Container.html',
        string $gridTemplate = 'EXT:container/Resources/Private/Templates/Grid.html',
        bool $saveAndCloseInNewContentElementWizard = true,
        bool $registerInNewContentElementWizard = true
    ): void {
        trigger_error('use "configureContainer" with a ContainerConfiguration Object!', E_USER_DEPRECATED);
        $configuration = (new ContainerConfiguration($cType, $label, $description, $grid))
            ->setIcon($icon)
            ->setBackendTemplate($backendTemplate)
            ->setGridTemplate($gridTemplate)
            ->setSaveAndCloseInNewContentElementWizard($saveAndCloseInNewContentElementWizard)
            ->setRegisterInNewContentElementWizard($registerInNewContentElementWizard);
        $this->configureContainer($configuration);
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
     * @deprecated
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
    ): void {
        trigger_error('use "configureContainer" instead of "registerContainer"', E_USER_DEPRECATED);
        $configuration = (new ContainerConfiguration($cType, $label, $description, $grid))
            ->setIcon($icon)
            ->setBackendTemplate($backendTemplate)
            ->setGridTemplate($gridTemplate)
            ->setRegisterInNewContentElementWizard($registerInNewContentElementWizard);
        $this->configureContainer($configuration);
    }

    /**
     * @param string $cType
     * @param int $colPos
     * @return array
     */
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

    /**
     * @param string $cType
     * @param int $colPos
     * @return array
     * @deprecated
     */
    public function getAllowedConfiguration(string $cType, int $colPos): array
    {
        trigger_error('should not be required, update EXT:content_defender to 3.1', E_USER_DEPRECATED);
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

    public function registerIcons(): void
    {
        if (isset($GLOBALS['TCA']['tt_content']['containerConfiguration']) && is_array($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
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
     */
    public function getGridPartialPaths(string $cType): array
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridPartialPaths'])) {
            return [];
        }
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridPartialPaths'];
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
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            return [];
        }
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
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            return [$TSdataArray, $id, $rootLine, $returnPartArray];
        }
        $TSdataArray['default'] = $this->getPageTsString();
        return [$TSdataArray, $id, $rootLine, $returnPartArray];
    }

    public function getPageTsString(): string
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            return '';
        }
        $pageTs = '';
        // group containers by group
        $groupedByGroup = [];
        $defaultGroup = 'container';
        foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $cType => $containerConfiguration) {
            if ($containerConfiguration['registerInNewContentElementWizard'] === true) {
                $group = $containerConfiguration['group'] !== '' ? $containerConfiguration['group'] : $defaultGroup;
                if (empty($groupedByGroup[$group])) {
                    $groupedByGroup[$group] = [];
                }
                $groupedByGroup[$group][$cType] = $containerConfiguration;
            }
            $pageTs .= LF . 'mod.web_layout.tt_content.preview {
' . $cType . ' = ' . $containerConfiguration['backendTemplate'] . '
}
';
        }
        foreach ($groupedByGroup as $group => $containerConfigurations) {
            $groupLabel = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups'][$group] ? $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups'][$group] : $group;

            $content = '
mod.wizards.newContentElement.wizardItems.' . $group . '.header = ' . $groupLabel . '
mod.wizards.newContentElement.wizardItems.' . $group . '.show = *
';
            foreach ($containerConfigurations as $cType => $containerConfiguration) {
                $content .= 'mod.wizards.newContentElement.wizardItems.' . $group . '.elements {
' . $cType . ' {
    title = ' . $containerConfiguration['label'] . '
    description = ' . $containerConfiguration['description'] . '
    iconIdentifier = ' . $cType . '
    tt_content_defValues.CType = ' . $cType . '
    saveAndClose = ' . $containerConfiguration['saveAndCloseInNewContentElementWizard'] . '
}
}
';
            }
            $pageTs .= LF . $content;
        }
        return $pageTs;
    }
}
