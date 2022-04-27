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
        'configurationToUseInTestInstance' => [
            'SYS' => ['features' => ['fluidBasedPageModule' => false]],
        ],
        'xmlDatabaseFixtures' => [
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_users.xml',
            'EXT:container/Tests/Acceptance/Fixtures/sys_language.xml',
            'EXT:container/Tests/Acceptance/Fixtures/pages.xml',
            'EXT:container/Tests/Acceptance/Fixtures/sys_workspace.xml',
            'EXT:container/Tests/Acceptance/Fixtures/tt_content.xml',
            'EXT:container/Tests/Acceptance/Fixtures/be_groups.xml',
        ],
    ];

    public function _initialize()
    {
        if (getenv('FLUID_BASED_PAGE_MODULE')) {
            $this->localConfig['configurationToUseInTestInstance']['SYS']['features']['fluidBasedPageModule'] = true;
        }
        parent::_initialize();
    }
}
