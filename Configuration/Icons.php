<?php

declare(strict_types=1);

call_user_func(static function () {
    $iconsToRegister = [
        'container-1col',
        'container-2col',
        'container-2col-left',
        'container-2col-right',
        'container-3col',
        'container-4col',
    ];
    $icons = [];
    foreach ($iconsToRegister as $icon) {
        $icons[$icon] = [
            'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            'EXT:container/Resources/Public/Icons/' . $icon . '.svg',
        ];
    }
    return $icons;
});
