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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('2cols-header-0');
        $I->see('header-header-0');
        $I->dontSee('2cols-header-1');
        $I->dontSee('header-header-1');
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
        $I->see('2cols-header-1');
        $I->see('header-header-1');
        $I->dontSee('2cols-header-0');
        $I->dontSee('header-header-0');

        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            $I->selectOption('select[name="actionMenu"]', 'Languages');
        } else {
            $I->selectOption('select[name="actionMenu"]', 'Language Comparison');
        }
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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithLocalization']);
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
        // we have a "Content" Button for new elements with Fluid based page module
        $I->dontSee('Content', '#element-tt_content-102 .t3-page-ce-body');
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            $I->selectOption('select[name="actionMenu"]', 'Languages');
        } else {
            $I->selectOption('select[name="actionMenu"]', 'Language Comparison');
        }
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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'emptyPage']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('Content');
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Content');
        } else {
            $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard-button').click()");
        }
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Container');
            $I->click('2 Column Container With Header');
        } else {
            $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard').filter('container')");
            $I->wait(0.5);
            $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard').shadowRoot.querySelector('button[data-identifier=\"container_b13-2cols-with-header-container\"]').click()");
        }
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
     * @throws \Exception
     */
    public function canDragAndDropElementOutsideIntoContainer(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainerAndElementOutsice']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->waitForElement('#element-tt_content-901');
        $dataColPos = $I->getDataColPos(900, 200);
        $I->waitForElement('#element-tt_content-900 [data-colpos="' . $dataColPos . '"] .t3js-page-ce-dropzone-available');
        $I->dontSeeElement('#element-tt_content-900 #element-tt_content-901');
        $I->dragAndDrop('#element-tt_content-901 .t3js-page-ce-draghandle', '#element-tt_content-900 [data-colpos="' . $dataColPos . '"] .t3js-page-ce-dropzone-available');
        $pageTree->openPath(['home', 'pageWithContainerAndElementOutsice']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForElement('#element-tt_content-901');
        $I->seeElement('#element-tt_content-900 #element-tt_content-901');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function newElementInHeaderColumnHasExpectedColPosAndParentSelected(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainer-2']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $dataColPos = $I->getDataColPos(700, 200);
        $I->waitForElement('#element-tt_content-700 [data-colpos="' . $dataColPos . '"]');
        $I->click('Content', '#element-tt_content-700 [data-colpos="' . $dataColPos . '"]');
        // "[data-colpos="700-200"]" can be attribute of "td" or "div" tag, depends if Fluid based page module is enabled
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->waitForText('Header Only');
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Header Only');
        } else {
            $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard').shadowRoot.querySelector('button[data-identifier=\"common_header\"]').click()");
        }
        $I->switchToContentFrame();
        $I->see('header [200]');
        $I->see('b13-2cols-with-header-container [700]');
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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(1, 200);
        $I->waitForElement('#element-tt_content-1 [data-colpos="' . $dataColPos . '"]');
        $selector = '#element-tt_content-1 div:nth-child(1) div:nth-child(2)';
        if ((new Typo3Version())->getMajorVersion() === 10) {
            $I->dontSee('english', $selector);
        } else {
            $I->dontSeeElement($selector . ' .t3js-flag[title="english"]');
        }
        $I->click('Content', '#element-tt_content-1 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->waitForText('Header Only');
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Header Only');
        } else {
            $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard').shadowRoot.querySelector('button[data-identifier=\"common_header\"]').click()");
        }
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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithLocalizationFreeModeWithContainer']);
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

        $uid = 104;

        $selector = '#element-tt_content-' . $uid . ' div:nth-child(1) div:nth-child(2)';
        $I->dontSee('german', $selector);
        $dataColPos = $I->getDataColPos($uid, 200);
        $I->click('Content', '#element-tt_content-' . $uid . ' [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->waitForText('Header Only');
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Header Only');
        } else {
            $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard').shadowRoot.querySelector('button[data-identifier=\"common_header\"]').click()");
        }
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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithTranslatedContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->waitForElement('select[name="actionMenu"]');
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
            $I->selectOption('select[name="actionMenu"]', 'Languages');
        } else {
            $I->selectOption('select[name="actionMenu"]', 'Language Comparison');
        }
        if ((new Typo3Version())->getMajorVersion() > 10) {
            if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 12) {
                $I->waitForElement('select[name="languageMenu"]');
                $I->selectOption('select[name="languageMenu"]', 'All languages');
            } else {
                $I->waitForText('Language');
                $I->click('Language');
                $I->waitForText('All languages');
                $I->click('All languages');
            }
        }
        $I->waitForElementVisible('a.t3js-localize');
        $I->click('a.t3js-localize');

        $I->switchToIFrame();
        $I->waitForElement('.t3js-localization-option');
        if ((new Typo3Version())->getMajorVersion() < 11) {
            $I->click('.t3js-localization-option');
            $I->click('Next');
            $I->waitForElement('div[data-slide="localize-summary"]');
        } else {
            $I->waitForElement('div[data-bs-slide="localize-summary"]');
        }
        $I->waitForText('(212) headerOfChild');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canTranslateChild(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithTranslatedContainer-2']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForElement('#element-tt_content-712');

        $I->click('headerOfChild', '#element-tt_content-712');

        $I->waitForElement('select[name="_langSelector"]');
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
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // b13-2cols-with-header-container container
        $I->waitForText('header');
        $I->see('header');
        $I->see('left side');
        $I->see('right side');
        // b13-2cols container
        $I->see('2-cols-left');
        $I->see('2-cols-right');
    }
}
