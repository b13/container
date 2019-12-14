<?php

$additionalColumns = [
    'tx_container_parent' => [
        'label' => 'Container',
        'config' => [
            'default' => 0,
            'type' => 'select',
            'itemsProcFunc' => \B13\Container\TcaContainerItems::class . '->listItemProcFunc',
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

$GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = \B13\Container\BackendLayoutView::class . '->colPosListItemProcFunc';
// copyAfterDuplFields colPos,sys_language_uid
// useColumnsForDefaultValues colPos,sys_language_uid,CType
$GLOBALS['TCA']['tt_content']['ctrl']['useColumnsForDefaultValues'] .= ',tx_container_parent';



\B13\Container\TcaRegistry::registerContainer(
    'foo',
    'bar',
    'bar-description',
    'container',
    [
        [
            ['name' => 'foo', 'colPos' => 100, 'colspan' => 2]
        ],
        [
            ['name' => 'foo2', 'colPos' => 101],
            ['name' => 'foo3', 'colPos' => 102]
        ]
    ]
);

$GLOBALS['TCA']['tt_content']['types']['foo']['showitem'] = 'sys_language_uid,CType,header,tx_container_parent,colPos';

