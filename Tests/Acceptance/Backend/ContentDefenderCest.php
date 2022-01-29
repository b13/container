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

class ContentDefenderCest
{

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');
    }

    public function canCreateChildIn2ColsContainerWithNoContentDefenderRestrictionsDefined(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content', '#element-tt_content-300 [data-colpos="300-200"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->see('Header Only');
        $I->see('Table');
    }

    public function doNotSeeNotAllowedContentElementsInNewContentElementWizard(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content', '#element-tt_content-1 [data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->wait(0.5);
        $I->see('Header Only');
        $I->dontSee('Table');
    }

    public function doNotSeeNotAllowedContentElementsInCTypeSelectBoxWhenCreateNewElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content', '#element-tt_content-1 [data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->wait(0.5);
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->wait(0.2);
        $I->see('textmedia', 'select');
        $I->dontSee('Table', 'select');
    }

    public function doNotSeeNotAllowedContentElementsInCTypeSelectBoxWhenEditAnElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'contentTCASelectCtype']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('#element-tt_content-502 a[title="Edit"]');
        $I->see('textmedia', 'select');
        $I->dontSee('Table', 'select');
    }

    public function canSeeNewContentButtonIfMaxitemsIsNotReached(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->see('Content', '#element-tt_content-402 [data-colpos="402-202"]');
    }

    public function canNotSeeNewContentButtonIfMaxitemsIsReached(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->dontSee('Content', '#element-tt_content-401 [data-colpos="401-202"]');
    }
}
