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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
    public function liveWorkspaceShowsLiveElements(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
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
    public function testWorkspaceShowsWorkspaceElements(BackendTester $I, PageTree $pageTree)
    {
        $this->switchToTestWs($I);
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
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
    public function liveWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            $I->waitForElement('select[name="languageMenu"]');
            $I->selectOption('select[name="languageMenu"]', 'german');
        } else {
            $I->waitForText('Language');
            $I->click('Language');
            $I->waitForText('german');
            $I->click('german');
        }
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('translation-live');
        $I->dontSee('translation-ws');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @group workspace
     */
    public function testWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree): void
    {
        $this->switchToTestWs($I);
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            $I->waitForElement('select[name="languageMenu"]');
            $I->selectOption('select[name="languageMenu"]', 'german');
        } else {
            $I->waitForText('Language');
            $I->click('Language');
            $I->waitForText('german');
            $I->click('german');
        }
        $I->waitForElementNotVisible('#t3js-ui-block');

        $I->dontSee('translation-live');
        $I->see('translation-ws');
        $this->switchToLiveWs($I);
    }

    /**
     * @group workspace
     */
    public function testWorkspaceShowsLiveContainerUidForContainerParentFieldWhenContainerIsAlreadyMoved(BackendTester $I, PageTree $pageTree)
    {
        $this->switchToTestWs($I);
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithWorkspace-movedContainer']);
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
        $I->switchToMainFrame();
        $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
        $I->canSee($ws, self::$topBarModuleSelector);
        $I->click($ws, self::$topBarModuleSelector);
    }
}
