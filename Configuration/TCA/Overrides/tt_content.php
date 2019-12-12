<?php

\B13\Container\TcaRegistry::registerContainer(
    'foo',
    'bar',
    'EXT:container/Resources/Public/Icons/Extension.svg',
    [
        [
            ['name' => 'foo', 'colPos' => 100, 'colspan' => 2]
        ],
        [
            ['name' => 'foo2', 'colPos' => 101],
            ['name' => 'foo3', 'colPos' => 102]
        ]
    ],
    'EXT:container/Resources/Private/Contenttypes/Backend/foo.html'
);

$GLOBALS['TCA']['tt_content']['types']['foo']['showitem'] = 'CType,header';

$additionalColumns = [

    'tx_container_parent' => [
        'label' => 'Color of the image overlay gradient',
        'config' => [
            'type' => 'input',
            'size' => 10,
        ]
    ]
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'tx_container_parent'
);


