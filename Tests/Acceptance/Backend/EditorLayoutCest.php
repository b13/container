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

class EditorLayoutCest
{
    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('editor');
    }

    public function canSeeNewContentButton(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13)
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithContainer-5']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithContainer-5']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(802, 200);
        // header
        $I->waitForElement('#element-tt_content-802 [data-colpos="' . $dataColPos . '"]');
        $I->see('Content', '#element-tt_content-802 [data-colpos="' . $dataColPos . '"]');
    }
}
