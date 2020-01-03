<?php
declare(strict_types = 1);
namespace B13\Container\Tests\Acceptance\Backend;


use B13\Container\Tests\Acceptance\Support\BackendTester;
use B13\Container\Tests\Acceptance\Support\PageTree;
use TYPO3\TestingFramework\Core\Acceptance\Helper\Topbar;


/**
 * Tests the styleguide backend module can be loaded
 */
class WorkspaceCest
{

    /**
     * Selector for the module container in the topbar
     *
     * @var string
     */
    public static $topBarModuleSelector = '#typo3-cms-workspaces-backend-toolbaritems-workspaceselectortoolbaritem';


    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspacex
     */
    public function liveWorkspaceShowsLiveElements(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');

        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->see('header-live');
        $I->dontSee('header-ws');
        $I->dontSee('header-new-ws');

        // $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function testWorkspaceShowsWorkspaceElements(BackendTester $I, PageTree $pageTree)
    {
        $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
        $I->canSee('test-ws', self::$topBarModuleSelector);
        $I->click('test-ws', self::$topBarModuleSelector);
        $I->click('Page');

        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->dontSee('header-live');
        $I->see('header-ws');
        $I->see('header-new-ws');
    }
}
