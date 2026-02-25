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
use Codeception\Attribute\Group;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Acceptance\Helper\Topbar;

class WorkspaceCest
{
    /**
     * Selector for the module container in the topbar
     */
    public static string $topBarModuleSelector = '#typo3-cms-workspaces-backend-toolbaritems-workspaceselectortoolbaritem';

    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');
    }

    #[Group('workspace')]
    public function liveWorkspaceShowsLiveElements(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('header-live');
        $I->see('header-live');
        $I->dontSee('header-ws');
        $I->dontSee('header-new-ws');
    }

    #[Group('workspace')]
    public function testWorkspaceShowsWorkspaceElements(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $this->switchToTestWs($I);
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('header-ws');
        $I->dontSee('header-live');
        $I->see('header-ws');
        $I->see('header-new-ws');
        $this->switchToLiveWs($I);
    }

    #[Group('workspace')]
    public function liveWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree): void
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->waitForText('translation-live');
        $I->see('translation-live');
        $I->dontSee('translation-ws');
    }

    #[Group('workspace')]
    public function testWorkspaceShowsLiveElementsForTranslations(BackendTester $I, PageTree $pageTree): void
    {
        $I->clickLayoutModuleButton();
        $this->switchToTestWs($I);
        $pageTree->openPath(['home', 'pageWithWorkspace']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->dontSee('translation-live');
        $I->see('translation-ws');
        $this->switchToLiveWs($I);
    }

    #[Group('workspace')]
    public function testWorkspaceShowsLiveContainerUidForContainerParentFieldWhenContainerIsAlreadyMoved(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $this->switchToTestWs($I);
        $pageTree->openPath(['home', 'pageWithWorkspace-movedContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // 600 is live uid (603 is ws uid)
        $dataColPos = $I->getDataColPos(600, 200);
        $I->waitForElement('td[data-colpos="' . $dataColPos . '"]');
        $this->switchToLiveWs($I);
    }

    protected function switchToLiveWs(BackendTester $I): void
    {
        $this->switchToWs($I, 'LIVE workspace');
        $I->wait(0.3);
    }

    protected function switchToTestWs(BackendTester $I): void
    {
        $this->switchToWs($I, 'test-ws');
        $I->wait(0.3);
    }

    protected function switchToWs(BackendTester $I, string $ws): void
    {
        if ($I->getTypo3MajorVersion() > 13 && (new Typo3Version())->getBranch() !== '14.1') {
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
