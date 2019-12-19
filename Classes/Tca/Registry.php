<?php

namespace B13\Container\Tca;

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
     * @param string $extKey
     * @param array $grid
     * @param string $backendTemplate
     * @return void
     */
    public function registerContainer(
        string $cType,
        string $label,
        string $description,
        string $extKey,
        array $grid = [],
        string $backendTemplate = 'EXT:container/Resources/Private/Templates/Container.html'
    ): void {

        ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [$label, $cType, $this->getIcon($cType, $extKey)]
        );

        foreach ($grid as $row) {
            foreach ($row as $column) {
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                    $column['name'],
                    $column['colPos']
                ];
            }
        }

        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType] = [
            'cType' => $cType,
            'extKey' => $extKey,
            'label' => $label,
            'description' => $description,
            'backendTemplate' => $backendTemplate,
            'grid' => $grid
        ];
    }

    /**
     * @return void
     */
    public function registerIcons(): void
    {
        if (is_array($GLOBALS['TCA']['tt_content']['containerConfiguration'])) {
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $containerConfiguration) {
                $iconRegistry->registerIcon(
                    $containerConfiguration['cType'],
                    SvgIconProvider::class,
                    ['source' => $this->getIcon($containerConfiguration['cType'], $containerConfiguration['extKey'])]
                );
            }
        }
    }

    /**
     * @param string $cType
     * @param string $extKey
     * @return string
     */
    protected function getIcon(string $cType, string $extKey): string
    {
        $icon = 'EXT:' . $extKey . '/Resources/Public/Icons/' . $cType . '.svg';
        if (!file_exists(GeneralUtility::getFileAbsFileName($icon))) {
            $icon = 'EXT:container/Resources/Public/Icons/Extension.svg';
        }
        return $icon;
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
     * @return array
     */
    public function getAvaiableColumns(string $cType): array
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
                $content .= 'mod.wizards.newContentElement.wizardItems.container.elements {
    ' . $cType . ' {
        title = ' . $containerConfiguration['label'] . '
        description = ' . $containerConfiguration['description'] . '
        iconIdentifier = ' . $cType . '
        tt_content_defValues.CType = ' . $cType . '
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
