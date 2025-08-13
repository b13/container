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
    public function canCreateChildIn2ColsContainerWithNoContentDefenderRestrictionsDefined(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithDifferentContainers']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(300, 200);
        $I->waitForElement('#element-tt_content-300 [data-colpos="' . $dataColPos . '"]');
        $newContentElementLabel = $I->getNewContentElementLabel();

        $I->click($newContentElementLabel, '#element-tt_content-300 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default\"]').click()");
        }
        $I->waitForText('Header Only');
        $I->see('Header Only');
        $I->see('Images Only');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInNewContentElementWizard(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithContainer-3']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithContainer-3']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(800, 200);
        $I->waitForElement('#element-tt_content-800 [data-colpos="' . $dataColPos . '"]');
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->click($newContentElementLabel, '#element-tt_content-800 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default\"]').click()");
        }
        $I->waitForText('Header Only');
        $I->dontSee('Images Only');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInNewContentElementWizardTriggeredByContextMenu(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithContainer-3']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithContainer-3']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->waitForElement('#element-tt_content-810 a.t3js-contextmenutrigger');
            $I->click('#element-tt_content-810 a.t3js-contextmenutrigger');
        } else {
            $I->waitForElement('#element-tt_content-800 [data-contextmenu-uid="810"]');
            $I->click('#element-tt_content-800 [data-contextmenu-uid="810"]');
        }
        switch ($typo3Version->getMajorVersion()) {
            case 11:
                $I->waitForText('More options...');
                $I->click('.list-group-item-submenu');
                $I->waitForText('\'Create New\' wizard');
                $I->click('#contentMenu1 [data-callback-action="newContentWizard"]');
                break;
            case 12:
                $I->waitForText('More options...');
                $I->click('.context-menu-item-submenu');
                $I->waitForText('\'Create New\' wizard');
                $I->click('#contentMenu1');
                break;
            default:
                // v13
                $I->switchToMainFrame();
                $I->waitForElementVisible('typo3-backend-context-menu button[data-contextmenu-id="root_more"]', 5);
                $I->click('button[data-contextmenu-id="root_more"]', 'typo3-backend-context-menu');
                $I->waitForElementVisible('typo3-backend-context-menu button[data-contextmenu-id="root_more_newWizard"]', 5);
                $I->click('button[data-contextmenu-id="root_more_newWizard"]', 'typo3-backend-context-menu');
                break;
        }

        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default\"]').click()");
        }
        $I->waitForText('Header Only');
        $I->dontSee('Images Only');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInCTypeSelectBoxWhenCreateNewElement(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithContainer-4']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithContainer-4']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(801, 200);
        $I->waitForElement('#element-tt_content-801 [data-colpos="' . $dataColPos . '"]');
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->click($newContentElementLabel, '#element-tt_content-801 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default\"]').click()");
        }
        $I->waitForText('Header Only');
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Header Only');
        } else {
            if ($typo3Version->getMajorVersion() < 13) {
                $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"common_header\"]').click()");
            } else {
                $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default_header\"]').click()");
            }
        }
        $I->switchToContentFrame();
        $I->wait(0.5);
        $I->see('textmedia', 'select');
        $I->dontSee('Images Only', 'select');
    }

    /**
     * @group content_defender
     */
    public function doNotSeeNotAllowedContentElementsInCTypeSelectBoxWhenEditAnElement(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'contentTCASelectCtype']);
        } else {
            $pageTreeV13->openPath(['home', 'contentTCASelectCtype']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $I->waitForElement('#element-tt_content-502 a[title="Edit"]');
        $I->click('#element-tt_content-502 a[title="Edit"]');
        $I->waitForElement('#EditDocumentController');
        $I->see('textmedia', 'select');
        $I->dontSee('Images Only', 'select');
    }

    /**
     * @group content_defender
     */
    public function canSeeNewContentButtonIfMaxitemsIsNotReached(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        } else {
            $pageTreeV13->openPath(['home', 'contentDefenderMaxitems']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(402, 202);
        $I->waitForElement('#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
        $I->see('Content', '#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
    }

    /**
     * @group content_defender
     */
    public function canNotSeeNewContentButtonIfMaxitemsIsReached(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        } else {
            $pageTreeV13->openPath(['home', 'contentDefenderMaxitems']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(401, 202);
        $I->waitForElement('#element-tt_content-401 [data-colpos="' . $dataColPos . '"]');
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->dontSee($newContentElementLabel, '#element-tt_content-401 [data-colpos="' . $dataColPos . '"]');
    }

    /**
     * @group content_defender
     */
    public function canCreateNewChildInContainerIfMaxitemsIsReachedInOtherContainer(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'contentDefenderMaxitems']);
        } else {
            $pageTreeV13->openPath(['home', 'contentDefenderMaxitems']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(402, 202);
        $I->waitForElement('#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->click($newContentElementLabel, '#element-tt_content-402 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->waitForElement('.modal-dialog');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default\"]').click()");
        }
        $I->waitForText('Header Only');
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $I->click('Header Only');
        } else {
            if ($typo3Version->getMajorVersion() < 13) {
                $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"common_header\"]').click()");
            } else {
                $I->executeJS("document.querySelector('" . $I->getNewRecordWizardSelector() . "').shadowRoot.querySelector('button[data-identifier=\"default_header\"]').click()");
            }
        }
        $I->switchToContentFrame();
        $I->waitForText('Create new Page Content on page');
        $I->seeElement('#EditDocumentController');
    }

    /**
     * @group content_defender
     */
    public function seeEditDocumentWhenAddingChildrenToColposWhereOnlyHeaderIsAllowed(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithDifferentContainers']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithDifferentContainers']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(300, 201);
        $I->waitForElement('#element-tt_content-300 [data-colpos="' . $dataColPos . '"]');
        $newContentElementLabel = $I->getNewContentElementLabel();
        $I->click($newContentElementLabel, '#element-tt_content-300 [data-colpos="' . $dataColPos . '"]');
        $I->switchToIFrame();
        $I->switchToContentFrame();
        $I->wait(0.5);
        $I->see('header', 'select');
        $I->dontSee('Images Only', 'select');
    }
}
