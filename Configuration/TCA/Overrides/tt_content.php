<?php

$additionalColumns = [
    'tx_container_parent' => [
        'label' => 'Container',
        'config' => [
            'default' => 0,
            'type' => 'select',
            'foreign_table' => 'tt_content',
            // do not render standard tt_content elements, but only elements returned from itemsProcFunc
            // only neccessary for TYPO3 9
            'foreign_table_where' => ' AND 1=2',
            'itemsProcFunc' => \B13\Container\Tca\ItemProcFunc::class . '->txContainerParent',
            'renderType' => 'selectSingle'
        ]
    ]
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
     $additionalColumns
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'general',
    'tx_container_parent'
);

$GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = \B13\Container\Tca\ItemProcFunc::class . '->colPos';

// copyAfterDuplFields colPos,sys_language_uid
// useColumnsForDefaultValues colPos,sys_language_uid,CType
// new element
$GLOBALS['TCA']['tt_content']['ctrl']['useColumnsForDefaultValues'] .= ',tx_container_parent';
// change properties in translation when move default element
// move child outside container reset parent
$GLOBALS['TCA']['tt_content']['ctrl']['copyAfterDuplFields'] .= ',tx_container_parent';
