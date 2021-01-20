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
    public function configureContainer(ContainerConfiguration $containerConfiguration)
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
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
					--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
				--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
					layout;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:layout_formlabel,
					--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
				--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,
					hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden,
					--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
				--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended
';

        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$containerConfiguration->getCType()] = $containerConfiguration->toArray();
    }

    /**
     * @param string $cType
     * @param int $colPos
     * @return array
     */
    public function getContentDefenderConfiguration($cType, $colPos)
    {
        $contentDefenderConfiguration = [];
        $rows = $this->getGrid($cType);
        foreach ($rows as $columns) {
            foreach ($columns as $column) {
                if ((int)$column['colPos'] === $colPos) {
                    $contentDefenderConfiguration['allowed.'] = (array)$column['allowed'];
                    $contentDefenderConfiguration['disallowed.'] = (array)$column['disallowed'];
                    $contentDefenderConfiguration['maxitems'] = (array)$column['maxitems'];
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
    public function getAllowedConfiguration($cType, $colPos)
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

    public function registerIcons()
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
    public function isContainerElement($cType)
    {
        return !empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]);
    }

    /**
     * @return array
     */
    public function getRegisteredCTypes()
    {
        return array_keys((array)$GLOBALS['TCA']['tt_content']['containerConfiguration']);
    }

    /**
     * @param string $cType
     * @return array
     */
    public function getGrid($cType)
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
    public function getGridTemplate($cType)
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridTemplate'])) {
            return null;
        }
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridTemplate'];
    }

    /**
     * @param string $cType
     * @param int $colPos
     * @return string|null
     */
    public function getColPosName($cType, $colPos)
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
    public function getAvailableColumns($cType)
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
    public function getAllAvailableColumns()
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
    public function addPageTS($TSdataArray, $id, $rootLine, $returnPartArray)
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            return [$TSdataArray, $id, $rootLine, $returnPartArray];
        }
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
