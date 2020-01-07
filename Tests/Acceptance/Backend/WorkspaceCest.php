<?php
declare(strict_types = 1);
namespace B13\Container\Tests\Acceptance\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Acceptance\Support\BackendTester;
use B13\Container\Tests\Acceptance\Support\PageTree;
use TYPO3\TestingFramework\Core\Acceptance\Helper\Topbar;

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
     * @group workspace
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
    }


    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function testWorkspaceShowsWorkspaceElements(BackendTester $I, PageTree $pageTree)
    {
        $this->switchToTestWs($I);
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->dontSee('header-live');
        $I->see('header-ws');
        $I->see('header-new-ws');
        $this->switchToLiveWs($I);
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @return void
     * @group workspace
     */
    public function liveWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectOption('select[name="languageMenu"]', 'german');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('translation-live');
        $I->dontSee('translation-ws');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @return void
     * @group workspace
     */
    public function testWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree): void
    {
        $this->switchToTestWs($I);
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectOption('select[name="languageMenu"]', 'german');
        $I->waitForElementNotVisible('#t3js-ui-block');

        $I->dontSee('translation-live');
        $I->see('translation-ws');
        $this->switchToLiveWs($I);
    }

    /**
     * @param BackendTester $I
     * @return void
     */
    protected function switchToLiveWs(BackendTester $I): void
    {
        $this->switchToWs($I, 'LIVE workspace');
    }

    /**
     * @param BackendTester $I
     * @return void
     */
    protected function switchToTestWs(BackendTester $I): void
    {
        $this->switchToWs($I, 'test-ws');
    }

    /**
     * @param BackendTester $I
     * @param string $ws
     * @return void
     */
    protected function switchToWs(BackendTester $I, string $ws): void
    {
        $I->switchToMainFrame();
        $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
        $I->canSee($ws, self::$topBarModuleSelector);
        $I->click($ws, self::$topBarModuleSelector);
    }
}
