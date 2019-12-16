<?php
declare(strict_types = 1);
namespace B13\Container\Tests\Acceptance\Backend;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use B13\Container\Tests\Acceptance\Support\BackendTester;
use B13\Container\Tests\Acceptance\Support\PageTree;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;

/**
 * Tests the styleguide backend module can be loaded
 */
class ModuleCest
{

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
     */
    public function overlayModeShowCorrectContentElements(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['page-10']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->see('2cols-header-0');
        $I->see('header-header-0');
    }



    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContainerContentElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['page-1']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
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
    public function newElementInHeaderColumnHasExpectedColPosAndParentSeletected(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['page-2']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->click('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
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
        $pageTree->openPath(['page-2']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->click('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        // todo element is visible

        // todo more tests
        /*
         * localization shows container colPos
         * new in edit element has default values
         */
    }

}
