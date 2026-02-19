<?php

declare(strict_types=1);

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
use B13\Container\Tests\Acceptance\Support\PageTreeV13;
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
        $I->loginAs('admin');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function liveWorkspaceShowsLiveElements(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->clickLayoutModuleButton();
        if ($I->getTypo3MajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithWorkspace']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithWorkspace']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('header-live');
        $I->see('header-live');
        $I->dontSee('header-ws');
        $I->dontSee('header-new-ws');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function testWorkspaceShowsWorkspaceElements(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->clickLayoutModuleButton();
        $this->switchToTestWs($I);
        if ($I->getTypo3MajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithWorkspace']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithWorkspace']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('header-ws');
        $I->dontSee('header-live');
        $I->see('header-ws');
        $I->see('header-new-ws');
        $this->switchToLiveWs($I);
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function liveWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->clickLayoutModuleButton();
        if ($I->getTypo3MajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithWorkspace']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithWorkspace']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->waitForText('translation-live');
        $I->see('translation-live');
        $I->dontSee('translation-ws');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function testWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->clickLayoutModuleButton();
        $this->switchToTestWs($I);
        if ($I->getTypo3MajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithWorkspace']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithWorkspace']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->dontSee('translation-live');
        $I->see('translation-ws');
        $this->switchToLiveWs($I);
    }

    /**
     * @group workspace
     */
    public function testWorkspaceShowsLiveContainerUidForContainerParentFieldWhenContainerIsAlreadyMoved(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->clickLayoutModuleButton();
        $this->switchToTestWs($I);
        if ($I->getTypo3MajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithWorkspace-movedContainer']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithWorkspace-movedContainer']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();
        // 600 is live uid (603 is ws uid)
        $dataColPos = $I->getDataColPos(600, 200);
        $I->waitForElement('td[data-colpos="' . $dataColPos . '"]');
        $this->switchToLiveWs($I);
    }

    /**
     * @param BackendTester $I
     */
    protected function switchToLiveWs(BackendTester $I): void
    {
        $this->switchToWs($I, 'LIVE workspace');
        $I->wait(0.3);
    }

    /**
     * @param BackendTester $I
     */
    protected function switchToTestWs(BackendTester $I): void
    {
        $this->switchToWs($I, 'test-ws');
        $I->wait(0.3);
    }

    /**
     * @param BackendTester $I
     * @param string $ws
     */
    protected function switchToWs(BackendTester $I, string $ws): void
    {
        if ($I->getTypo3MajorVersion() > 13) {
            $I->switchToMainFrame();
            if ($ws === 'test-ws') {
                $I->executeJS("document.querySelector('typo3-backend-workspace-selector #workspace-menu button[title=\"test-ws\"]').click();");
            } else {
                // first button
                $I->executeJS("document.querySelector('typo3-backend-workspace-selector #workspace-menu button').click();");
            }
        } else {
            $I->switchToMainFrame();
            $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
            $I->canSee($ws, self::$topBarModuleSelector);
            $I->click($ws, self::$topBarModuleSelector);
        }
    }
}
