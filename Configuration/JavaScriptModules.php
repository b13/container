<?php

if (\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion() < 13) {
    return [
        'imports' => [
            '@typo3/backend/layout-module/drag-drop.js' => 'EXT:container/Resources/Public/JavaScript/Overrides12/drag-drop.js',
            '@typo3/backend/layout-module/paste.js' => 'EXT:container/Resources/Public/JavaScript/Overrides12/paste.js',
        ],
    ];
}
return [
    'imports' => [
        '@typo3/backend/layout-module/drag-drop.js' => 'EXT:container/Resources/Public/JavaScript/Overrides/drag-drop.js',
        '@typo3/backend/layout-module/paste.js' => 'EXT:container/Resources/Public/JavaScript/Overrides/paste.js',
    ],
];
