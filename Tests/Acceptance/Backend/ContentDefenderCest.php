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

class ContentDefenderCest
{
    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');
    }

    /**
     * @group content_defender
     */
    public function canCreateChildIn2ColsContainerWithNoContentDefenderRestrictionsDefined(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(300, 200);
        $I->waitForElement('#element-tt_content-300 [data-colpos="' . $dataColPos . '"]');
        $I->click('Content', '#element-tt_content-300 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->waitForText('Header Only');
        $I->see('Header Only');
        $I->see('Table');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInNewContentElementWizard(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainer-3']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(800, 200);
        $I->waitForElement('#element-tt_content-800 [data-colpos="' . $dataColPos . '"]');
        $I->click('Content', '#element-tt_content-800 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        $I->waitForText('Header Only');
        $I->dontSee('Table');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInCTypeSelectBoxWhenCreateNewElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainer-4']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(801, 200);
        $I->waitForElement('#element-tt_content-801 [data-colpos="' . $dataColPos . '"]');
        $I->click('Content', '#element-tt_content-801 [data-colpos="' . $dataColPos . '"]');
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
        $I->wait(0.5);
        $I->see('textmedia', 'select');
        $I->dontSee('Table', 'select');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInCTypeSelectBoxWhenEditAnElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'contentTCASelectCtype']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $I->waitForElement('#element-tt_content-502 a[title="Edit"]');
        $I->click('#element-tt_content-502 a[title="Edit"]');
        $I->waitForElement('#EditDocumentController');
        $I->see('textmedia', 'select');
        $I->dontSee('Table', 'select');
    }

    /**
     * @group content_defender
     */
    public function canSeeNewContentButtonIfMaxitemsIsNotReached(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(402, 202);
        $I->waitForElement('#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
        $I->see('Content', '#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
    }

    /**
     * @group content_defender
     */
    public function canNotSeeNewContentButtonIfMaxitemsIsReached(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(401, 202);
        $I->waitForElement('#element-tt_content-401 [data-colpos="' . $dataColPos . '"]');
        $I->dontSee('Content', '#element-tt_content-401 [data-colpos="' . $dataColPos . '"]');
    }

    /**
     * @group content_defender
     */
    public function canCreateNewChildInContainerIfMaxitemsIsReachedInOtherContainer(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(402, 202);
        $I->waitForElement('#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
        $I->click('Content', '#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
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
        $I->waitForText('Create new Page Content on page');
        $I->seeElement('#EditDocumentController');
    }
}
