<?php

namespace B13\Container;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


class TcaRegistry
{

    public static function registerContainer (
        string $cType,
        string $label,
        string $icon,
    array $grid = [],
    string $backendTemplate = 'EXT:container/Resources/Private/Contenttypes/Backend/container.html'
    ): void
    {

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
        description = ' . $label . '
        iconIdentifier = content-header
        tt_content_defValues.CType = ' . $cType . '
    }
}
';

        $pageTS .= 'mod.web_layout.tt_content.preview {
    ' . $cType . ' = ' . $backendTemplate . '
}
';

        /*
        foreach ($grid as $row) {
            foreach($row as $column) {
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][] = [
                    $column['name'], $column['colPos']
                ];
            }
        }
        */


        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['pageTS'] = $pageTS;
        $GLOBALS['TCA']['tt_content']['containerConfiguration'][$cType]['grid'] = $grid;



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
