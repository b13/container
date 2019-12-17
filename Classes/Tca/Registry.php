<?php

namespace B13\Container\Tca;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class Registry implements SingletonInterface
{

    public function registerContainer (
        string $cType,
        string $label,
        string $description,
    string $extKey,
    array $grid = [],
    string $backendTemplate = 'EXT:container/Resources/Private/Contenttypes/Backend/container.html'
    ): void
    {

        $icon = 'EXT:' . $extKey . '/Resources/Public/Icons/' . $cType . '.svg';
        if (!file_exists(GeneralUtility::getFileAbsFileName($icon))) {
            $icon = 'EXT:container/Resources/Public/Icons/Extension.svg';
        }
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            $cType,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => $icon
            ]
        );
        #return;
        ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [$label, $cType, $icon]
        );

        $pageTS = '';
        $pageTS .= 'mod.wizards.newContentElement.wizardItems.container.elements {
    ' . $cType . ' {
        title = ' . $label . '
        description = ' . $description . '
        iconIdentifier = ' . $cType . '
        tt_content_defValues.CType = ' . $cType . '
    }
}
';

        $pageTS .= 'mod.web_layout.tt_content.preview {
    ' . $cType . ' = ' . $backendTemplate . '
}
';


        foreach ($grid as $row) {
            foreach($row as $column) {
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                    $column['name'], $column['colPos']
                ];
            }
        }



        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['pageTS'] = $pageTS;
        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'] = $grid;

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
        foreach ($GLOBALS['TCA']['tt_content']['containerConfiguration'] as $cTypeConfiguration) {
            $grid = $cTypeConfiguration['grid'];
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
                if (!empty($containerConfiguration['pageTS'])) {
                    $content .= $containerConfiguration['pageTS'];
                }
            }
            $TSdataArray['default'] .= LF . $content;
        }
        return [$TSdataArray, $id, $rootLine, $returnPartArray];
    }
}
