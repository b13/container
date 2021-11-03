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

class LayoutCest
{

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
     */
    public function connectedModeShowCorrectContentElements(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');

        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->see('2cols-header-0');
        $I->see('header-header-0');
        $I->dontSee('2cols-header-1');
        $I->dontSee('header-header-1');
        $I->selectOption('select[name="languageMenu"]', 'german');

        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('2cols-header-1');
        $I->see('header-header-1');
        $I->dontSee('2cols-header-0');
        $I->dontSee('header-header-0');

        $I->selectOption('select[name="actionMenu"]', 'Languages');
        $I->waitForElementNotVisible('#t3js-ui-block');

        // td.t3-grid-cell:nth-child(1)
        // default language
        $languageCol = 'td.t3-grid-cell:nth-child(1)';
        $I->see('2cols-header-0', $languageCol);
        $I->see('header-header-0', $languageCol . ' td.t3-grid-cell');
        $I->dontSee('2cols-header-1', $languageCol);
        $I->dontSee('header-header-1', $languageCol . ' td.t3-grid-cell');
        //td.t3-grid-cell:nth-child(2)
        // german language
        $languageCol = 'td.t3-grid-cell:nth-child(2)';
        $I->see('2cols-header-1', $languageCol);
        $I->see('header-header-1', $languageCol . ' td.t3-grid-cell');
        $I->dontSee('2cols-header-0', $languageCol);
        $I->dontSee('header-header-0', $languageCol . ' td.t3-grid-cell');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function connectedModeShowNoAddContentButton(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectOption('select[name="languageMenu"]', 'german');
        $I->waitForElementNotVisible('#t3js-ui-block');
        // we have a "Content" Button for new elements with Fluid based page module
        $I->dontSee('Content', '#element-tt_content-102 .t3-page-ce-body');
        $I->selectOption('select[name="actionMenu"]', 'Languages');
        $I->waitForElementNotVisible('#t3js-ui-block');
        // but not in Language View
        $I->dontSee('Content', '#element-tt_content-102');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContainerContentElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'emptyPage']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->click('Container');
        $I->click('2 Column Container With Header');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSee('header', '.t3-grid-container');
        $I->canSee('left side', '.t3-grid-container');
        $I->canSee('right side', '.t3-grid-container');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function newElementInHeaderColumnHasExpectedColPosAndParentSelected(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->click('Content', '#element-tt_content-1 [data-colpos="1-200"]');
        // "[data-colpos="1-200"]" can be attribute of "td" or "div" tag, depends if Fluid based page module is enabled
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->see('header [200]');
        $I->see('b13-2cols-with-header-container [1]');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContentElementInContainer(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $selector = '#element-tt_content-1 div:nth-child(1) div:nth-child(2)';
        if ((new Typo3Version())->getMajorVersion() === 10) {
            $I->dontSee('english', $selector);
        } else {
            $I->dontSeeElement($selector . ' .t3js-flag[title="english"]');
        }
        $I->click('Content', '#element-tt_content-1 [data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        if ((new Typo3Version())->getMajorVersion() === 10) {
            $I->see('english', $selector);
        } else {
            $I->canSeeElement($selector . ' .t3js-flag[title="english"]');
        }
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContentElementInTranslatedContainerInFreeMode(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithLocalizationFreeModeWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->selectOption('select[name="languageMenu"]', 'german');
        $I->waitForElementNotVisible('#t3js-ui-block');

        $uid = 104;

        $selector = '#element-tt_content-' . $uid . ' div:nth-child(1) div:nth-child(2)';
        $I->dontSee('german', $selector);
        $I->click('Content', '#element-tt_content-' . $uid . ' [data-colpos="' . $uid . '-200"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        if ((new Typo3Version())->getMajorVersion() === 10) {
            $I->see('german', $selector);
        } else {
            $I->canSeeElement($selector . ' .t3js-flag[title="german"]');
        }
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canTranslateChildWithTranslationModule(BackendTester $I, PageTree $pageTree): void
    {
        // test must be before canTranslateChild
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithTranslatedContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->selectOption('select[name="actionMenu"]', 'Languages');
        if ((new Typo3Version())->getMajorVersion() > 10) {
            $I->selectOption('select[name="languageMenu"]', 'All languages');
        }
        $I->waitForElementVisible('a.t3js-localize');
        $I->click('a.t3js-localize');

        $I->switchToIFrame();
        $I->waitForElement('.t3js-localization-option');
        $I->click('.t3js-localization-option');
        $I->click('Next');
        $I->wait(1);
        $I->see('(212) headerOfChild');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canTranslateChild(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithTranslatedContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->click('headerOfChild', '#element-tt_content-212');

        $I->selectOption('select[name="_langSelector"]', 'german [NEW]');
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() === 10) {
            $I->see('[Translate to language-1:] headerOfChild');
        } else {
            $I->see('[Translate to german:] headerOfChild');
        }
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function canSeeContainerColumnTitleForDifferentContainers(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // b13-2cols-with-header-container container
        $I->see('header');
        $I->see('left side');
        $I->see('right side');
        // b13-2cols container
        $I->see('2-cols-left');
        $I->see('2-cols-right');
    }
}
