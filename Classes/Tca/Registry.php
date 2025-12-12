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

use B13\Container\Events\BeforeContainerConfigurationIsAppliedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Registry implements SingletonInterface
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ContainerConfiguration $containerConfiguration
     */
    public function configureContainer(ContainerConfiguration $containerConfiguration): void
    {
        $beforeContainerConfigurationIsAppliedEvent = new BeforeContainerConfigurationIsAppliedEvent($containerConfiguration);
        $this->eventDispatcher->dispatch($beforeContainerConfigurationIsAppliedEvent);
        if ($beforeContainerConfigurationIsAppliedEvent->shouldBeSkipped()) {
            return;
        }
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
            ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    'label' => $containerConfiguration->getLabel(),
                    'value' => $containerConfiguration->getCType(),
                    'icon' => $containerConfiguration->getCType(),
                    'group' => $containerConfiguration->getGroup(),
                    'description' => $containerConfiguration->getDescription(),
                ]
            );
        } else {
            ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    $containerConfiguration->getLabel(),
                    $containerConfiguration->getCType(),
                    $containerConfiguration->getCType(),
                    $containerConfiguration->getGroup(),
                ]
            );
        }
        $GLOBALS['TCA']['tt_content']['types'][$containerConfiguration->getCType()]['previewRenderer'] = \B13\Container\Backend\Preview\ContainerPreviewRenderer::class;

        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 13) {
            if (!isset($GLOBALS['TCA']['tt_content']['types'][$containerConfiguration->getCType()]['creationOptions'])) {
                $GLOBALS['TCA']['tt_content']['types'][$containerConfiguration->getCType()]['creationOptions'] = [];
            }
            $GLOBALS['TCA']['tt_content']['types'][$containerConfiguration->getCType()]['creationOptions']['saveAndClose'] =
                $containerConfiguration->getSaveAndCloseInNewContentElementWizard();
        }
        foreach ($containerConfiguration->getGrid() as $row) {
            foreach ($row as $column) {
                if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
                    $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                        'label' => $column['name'],
                        'value' => $column['colPos'],
                    ];
                } else {
                    $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                        $column['name'],
                        $column['colPos'],
                    ];
                }
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
                    return $contentDefenderConfiguration;
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

    public function registerIcons(): void
    {
        if (isset($GLOBALS['TCA']['tt_content']['containerConfiguration']) && is_array($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $containerConfiguration) {
                if (file_exists(GeneralUtility::getFileAbsFileName($containerConfiguration['icon']))) {
                    $provider = BitmapIconProvider::class;
                    if (str_contains($containerConfiguration['icon'], '.svg')) {
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

    public function isContainerElement(string $cType): bool
    {
        return !empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]);
    }

    public function getRegisteredCTypes(): array
    {
        return array_keys((array)($GLOBALS['TCA']['tt_content']['containerConfiguration'] ?? []));
    }

    public function getGrid(string $cType): array
    {
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'] ?? [];
    }

    public function getGridTemplate(string $cType): ?string
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridTemplate'])) {
            return null;
        }
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridTemplate'];
    }

    public function getGridPartialPaths(string $cType): array
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridPartialPaths'])) {
            return [];
        }
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridPartialPaths'];
    }

    public function getGridLayoutPaths(string $cType): array
    {
        return $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['gridLayoutPaths'] ?? [];
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

    public function getPageTsString(): string
    {
        if (empty($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            return '';
        }
        $pageTs = '';
        // group containers by group
        $groupedByGroup = [];
        $defaultGroup = 'container';

        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $cTypesExcludedInNewContentElementWizard = [];

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
            // s. https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-102834-RemoveItemsFromNewContentElementWizard.html
            if ($typo3Version->getMajorVersion() > 12) {
                if ($containerConfiguration['registerInNewContentElementWizard'] === false) {
                    $group = $containerConfiguration['group'] !== '' ? $containerConfiguration['group'] : $defaultGroup;
                    $cTypesExcludedInNewContentElementWizard[$group][] = $cType;
                }
            }
        }

        if ($typo3Version->getMajorVersion() > 12 && !empty($cTypesExcludedInNewContentElementWizard)) {
            foreach ($cTypesExcludedInNewContentElementWizard as $group => $ctypes) {
                $pageTs .= LF . 'mod.wizards.newContentElement.wizardItems.' . $group . '.removeItems := addToList(' . implode(',', $ctypes) . ')';
            }
            return $pageTs;
        }

        foreach ($groupedByGroup as $group => $containerConfigurations) {
            $groupLabel = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups'][$group] ?? $group;

            $content = '';
            if (!in_array($group, ['common', 'default', 'menu', 'special', 'forms', 'plugins'])) {
                // do not override EXT:backend dummy placeholders for item groups
                $content .= '
mod.wizards.newContentElement.wizardItems.' . $group . '.header = ' . $groupLabel . '
';
            }
            foreach ($containerConfigurations as $cType => $containerConfiguration) {
                array_walk($containerConfiguration['defaultValues'], static function (&$item, $key) {
                    $item = $key . ' = ' . $item;
                });
                $ttContentDefValues = 'CType = ' . $cType . LF . implode(LF, $containerConfiguration['defaultValues']);
                $content .= 'mod.wizards.newContentElement.wizardItems.' . ($group === 'default' ? 'common' : $group) . '.show := addToList(' . $cType . ')
';
                $content .= 'mod.wizards.newContentElement.wizardItems.' . ($group === 'default' ? 'common' : $group) . '.elements {
' . $cType . ' {
    title = ' . $containerConfiguration['label'] . '
    description = ' . $containerConfiguration['description'] . '
    iconIdentifier = ' . $cType . '
    tt_content_defValues {
    ' . $ttContentDefValues . '
    }
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
