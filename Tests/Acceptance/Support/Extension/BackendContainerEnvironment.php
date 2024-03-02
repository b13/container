<?php

declare(strict_types=1);

namespace B13\Container\Tests\Acceptance\Support\Extension;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Codeception\Event\SuiteEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Acceptance\Extension\BackendEnvironment;

class BackendContainerEnvironment extends BackendEnvironment
{
    /**
     * @var array
     */
    protected $localConfig = [
        'coreExtensionsToLoad' => [
            'core',
            'extbase',
            'fluid',
            'backend',
            'install',
            'frontend',
            'recordlist',
            'workspaces',
            'info',
        ],
        'pathsToLinkInTestInstance' => [
            'typo3conf/ext/container/Build/sites' => 'typo3conf/sites',
        ],
        'testExtensionsToLoad' => [
            'typo3conf/ext/container',
            'typo3conf/ext/container_example',
            'typo3conf/ext/content_defender',
        ],
        'csvDatabaseFixtures' => [
            __DIR__ . '/../../Fixtures/be_users.csv',
            __DIR__ . '/../../Fixtures/contentDefenderMaxitems.csv',
            __DIR__ . '/../../Fixtures/contentTCASelectCtype.csv',
            __DIR__ . '/../../Fixtures/emptyPage.csv',
            __DIR__ . '/../../Fixtures/pageWithContainer.csv',
            __DIR__ . '/../../Fixtures/pageWithContainer-2.csv',
            __DIR__ . '/../../Fixtures/pageWithDifferentContainers.csv',
            __DIR__ . '/../../Fixtures/pageWithLocalization.csv',
            __DIR__ . '/../../Fixtures/pageWithLocalizationFreeModeWithContainer.csv',
            __DIR__ . '/../../Fixtures/pageWithTranslatedContainer.csv',
            __DIR__ . '/../../Fixtures/pageWithTranslatedContainer-2.csv',
            __DIR__ . '/../../Fixtures/pageWithContainer-3.csv',
            __DIR__ . '/../../Fixtures/pageWithContainer-4.csv',
            __DIR__ . '/../../Fixtures/pageWithContainer-5.csv',
            __DIR__ . '/../../Fixtures/pageWithContainer-6.csv',
            __DIR__ . '/../../Fixtures/pageWithWorkspace.csv',
            __DIR__ . '/../../Fixtures/pageWithWorkspace-movedContainer.csv',
            __DIR__ . '/../../Fixtures/pageWithContainerAndContentElementOutside.csv',
            __DIR__ . '/../../Fixtures/pages.csv',
            __DIR__ . '/../../Fixtures/sys_workspace.csv',
            __DIR__ . '/../../Fixtures/be_groups.csv',
        ],
    ];

    public function _initialize(): void
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() === 13) {
            $this->localConfig['testExtensionsToLoad'] = [
                'typo3conf/ext/container',
                'typo3conf/ext/container_example',
            ];
        }
        parent::_initialize();
    }

    public function bootstrapTypo3Environment(SuiteEvent $suiteEvent): void
    {
        parent::bootstrapTypo3Environment($suiteEvent);
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 13) {
            return;
        }
        $content = "<?php

call_user_func(static function () {
    \$classLoader = require __DIR__ . '/../../../../../..' . '/vendor/autoload.php';
    \TYPO3\TestingFramework\Core\SystemEnvironmentBuilder::run(1, \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE);
    \TYPO3\CMS\Core\Core\Bootstrap::init(\$classLoader)->get(\TYPO3\CMS\Backend\Http\Application::class)->run();
});";
        $instancePath = ORIGINAL_ROOT . 'typo3temp/var/tests/acceptance';
        file_put_contents($instancePath . '/typo3/index.php', $content);
    }
}
