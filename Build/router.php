<?php

if (str_contains($_SERVER['REQUEST_URI'], 'language/domain')) {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $classLoader = require dirname(__DIR__) . '/vendor/autoload.php';
    \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::run();

    $isInstallToolDirectAccess = false;
    $container = \TYPO3\CMS\Core\Core\Bootstrap::init($classLoader, $isInstallToolDirectAccess);

    if ($container->has(\TYPO3\CMS\Core\Http\Application::class)) {
        $container->get(\TYPO3\CMS\Core\Http\Application::class)->run();
        return;
    }
    $container->get(\TYPO3\CMS\Install\Http\Application::class)->run();
}
return false;
