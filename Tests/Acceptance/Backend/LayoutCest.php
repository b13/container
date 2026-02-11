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
use Codeception\Scenario;

class LayoutCest
{
    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');
    }

    public function connectedModeShowCorrectContentElements(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText('2cols-header-0');
        $I->see('header-header-0');
        $I->dontSee('2cols-header-1');
        $I->dontSee('header-header-1');
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('2cols-header-1');
        $I->see('header-header-1');
        $I->dontSee('2cols-header-0');
        $I->dontSee('header-header-0');

        $I->selectLanguageComparisonMode();
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

    public function connectedModeShowNoAddContentButton(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');
        // we have a "Content" Button for new elements with Fluid based page module
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->dontSee($newContentElementLabel, '#element-tt_content-102 .t3-page-ce-body');
        if ($I->getTypo3MajorVersion() < 14) {
            $I->selectOption('select[name="actionMenu"]', 'Language Comparison');
        } else {
            $I->waitForElementVisible('.module-docheader-buttons .btn-group button.dropdown-toggle');
            $I->click('.module-docheader-buttons .btn-group button.dropdown-toggle');
            $I->waitForElementVisible('.module-docheader-buttons .dropdown-menu');
            $I->click('Language Comparison', '.module-docheader-buttons .dropdown-menu');
        }
        $I->waitForElementNotVisible('#t3js-ui-block');
        // but not in Language View
        $I->dontSee($newContentElementLabel, '#element-tt_content-102');
    }

    public function canCreateContainerContentElement(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'emptyPage']);
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText($newContentElementLabel);
        $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard-button').click()");
        $I->switchToIFrame();
        $I->waitForModal();
        $I->wait(0.5);
        if ($I->getTypo3MajorVersion() > 13) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"container\"]').click()");
        } else {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('container')");
        }
        $I->wait(0.5);
        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"container_b13-2cols-with-header-container\"]').click()");
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSee('my-default-value-header', '.element-preview-header-header');
        $I->canSee('header', '.t3-grid-container');
        $I->canSee('left side', '.t3-grid-container');
        $I->canSee('right side', '.t3-grid-container');
    }

    public function canCreateContainerContentElementSaveAndClose(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'emptyPage']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->waitForText($newContentElementLabel);

        $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard-button').click()");
        $I->switchToIFrame();
        $I->waitForModal();

        if ($I->getTypo3MajorVersion() > 13) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"container\"]').click()");
        } else {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('container')");
        }
        $I->wait(0.5);
        // test event listener
        // b13-2cols
        // this also tests container-example eventListener
        // https://github.com/b13/container-example/commit/df2560e75966a73754b5d4ea091d14727c16f024
        $I->waitForText('mod -- Some Description of the Container');
        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"container_b13-2cols\"]').click()");

        $I->switchToContentFrame();
        $I->waitForText('2-cols-left');
        $I->canSee('2-cols-left', '.t3-grid-container');
    }

    public function canDragAndDropElementOutsideIntoContainer(BackendTester $I, PageTree $pageTree, Scenario $scenario)
    {
        $I->clickLayoutModuleButton();
        $scenario->skip('drag and drop currently not work, s git show 4f459c2925be702ce93f047d7af32d296de1ddd6 Tests/Acceptance/Support/Helper/Mouse.php');
    }

    public function newElementInHeaderColumnHasExpectedColPosAndParentSelected(BackendTester $I, PageTree $pageTree): void
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithContainer-2']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $dataColPos = $I->getDataColPos(700, 200);
        $colPosSelector = '#element-tt_content-700 [data-colpos="' . $dataColPos . '"]';
        $I->clickNewContentElement($colPosSelector);
        // "[data-colpos="700-200"]" can be attribute of "td" or "div" tag, depends if Fluid based page module is enabled
        $I->switchToIFrame();
        $I->waitForModal();

        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('header ')");
        $I->waitForText('Header Only');
        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default_header\"]').click()");

        $I->switchToContentFrame();
        $I->see('header [200]');
        $I->see('2 Column Container With Header [700]');
    }

    public function canCreateContentElementInContainer(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(1, 200);
        $containerColumn = '#element-tt_content-1 [data-colpos="' . $dataColPos . '"]';
        $contentInContainerColumn = '#element-tt_content-1 div[data-colpos="' . $dataColPos . '"] .t3-page-ce';
        $I->waitForElement($containerColumn);
        $I->dontSeeElement($contentInContainerColumn);
        $I->clickNewContentElement($containerColumn);
        $I->switchToIFrame();
        $I->waitForModal();

        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('header ')");
        $I->waitForText('Header Only');
        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default_header\"]').click()");

        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSeeElement($contentInContainerColumn);
    }

    public function canCreateContentElementInTranslatedContainerInFreeMode(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithLocalizationFreeModeWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectGermanInLanguageMenu();
        $I->waitForElementNotVisible('#t3js-ui-block');

        $uid = 104;

        $selector = '#element-tt_content-' . $uid . ' div:nth-child(1) div:nth-child(2)';
        $I->dontSee('german', $selector);
        $dataColPos = $I->getDataColPos($uid, 200);
        $colPosSelector = '#element-tt_content-' . $uid . ' [data-colpos="' . $dataColPos . '"]';
        $I->clickNewContentElement($colPosSelector);
        $I->switchToIFrame();
        $I->waitForModal();

        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('header ')");
        $I->waitForText('Header Only');
        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default_header\"]').click()");

        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSeeElement($selector . ' .t3js-flag[title="german"]');
    }

    public function canTranslateChildWithTranslationModule(BackendTester $I, PageTree $pageTree, Scenario $scenario): void
    {
        // test must be before canTranslateChild
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithTranslatedContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->selectLanguageComparisonMode();
        $I->selectGermanInLanguageMenu();
        if ($I->getTypo3MajorVersion() < 14) {
            $I->waitForElementVisible('a.t3js-localize');
            $I->click('a.t3js-localize');
        } else {
            $I->waitForText('Translate');
            $I->executeJS("document.querySelector('#PageLayoutController typo3-backend-localization-button').click()");
        }

        $I->switchToIFrame();
        if ($I->getTypo3MajorVersion() < 14) {
            $I->waitForText('(212) headerOfChild');
        } else {
            $I->waitForText('headerOfChild');
        }
    }

    public function canTranslateChild(BackendTester $I, PageTree $pageTree): void
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithTranslatedContainer-2']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForElement('#element-tt_content-712');

        $I->click('headerOfChild', '#element-tt_content-712');

        if ($I->getTypo3MajorVersion() > 13) {
            $I->waitForText('english');
            $I->click('.module-docheader-bar-column button');
            $I->waitForText('german');
            $I->executeJS("document.querySelector('typo3-backend-localization-button').click()");
            $I->switchToIFrame();
            $I->waitForText('Localize');
            $I->executeJS("document.querySelector('typo3-backend-localization-wizard button.btn-primary').click()");
            $I->waitForText('Finish');
            $I->executeJS("document.querySelector('typo3-backend-localization-wizard').querySelector('button.btn-primary').click()");
            $I->switchToContentFrame();
        } else {
            $I->waitForElement('select[name="_langSelector"]');
            $I->selectOption('select[name="_langSelector"]', 'german [NEW]');
        }
        $I->see('[Translate to german:] headerOfChild');
    }

    public function canSeeContainerColumnTitleForDifferentContainers(BackendTester $I, PageTree $pageTree): void
    {
        $I->clickLayoutModuleButton();
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

    public function canSeeCustomBackendTemplate(BackendTester $I, PageTree $pageTree, Scenario $scenario): void
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForElement('#tx-container-example-custom-backend-template');
        $I->see('custom backend template');
    }

    public function canSeeDescriptionOfContainerInNewContentElementWizard(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'emptyPage']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->waitForText($newContentElementLabel);
        $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard-button').click()");
        $I->switchToIFrame();
        $I->waitForModal();

        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('container')");
        $I->wait(0.5);

        $I->see('Some Description of the Container');
    }

    public function canDisableContainerContentElementInNewContentElementWizard(BackendTester $I, PageTree $pageTree)
    {
        $I->clickLayoutModuleButton();
        $pageTree->openPath(['home', 'emptyPage']);
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->waitForText($newContentElementLabel);
        $I->executeJS("document.querySelector('typo3-backend-new-content-element-wizard-button').click()");
        $I->switchToIFrame();
        $I->waitForModal();
        $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').filter('container')");
        $I->wait(0.5);
        $I->see('2 Column Container With Header');
        $I->dontSee('1 Column');
    }
}
