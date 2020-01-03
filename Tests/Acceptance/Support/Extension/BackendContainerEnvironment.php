<?php
declare(strict_types=1);
namespace B13\Container\Tests\Acceptance\Support\Extension;


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
            'about',
            'install',
            'frontend',
            'recordlist',
            'workspaces'
        ],
        'pathsToLinkInTestInstance' => [
            'typo3conf/ext/container/Build/sites' => 'typo3conf/sites'
        ],
        'testExtensionsToLoad' => [
            'typo3conf/ext/container',
            'typo3conf/ext/container_example'
        ],
        'xmlDatabaseFixtures' => [
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_users.xml',
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_sessions.xml',
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_groups.xml',
            'EXT:container/Tests/Acceptance/Fixtures/sys_language.xml',
            'EXT:container/Tests/Acceptance/Fixtures/pages.xml',
            'EXT:container/Tests/Acceptance/Fixtures/sys_workspace.xml',
            'EXT:container/Tests/Acceptance/Fixtures/tt_content.xml'
        ],
    ];
}
