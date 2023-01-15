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

class EditorLayoutCest
{

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('editor');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canSeeNewContentButton(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainer-5']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $dataColPos = $I->getDataColPos(802, 200);
        // header
        $I->waitForElement('#element-tt_content-802 [data-colpos="' . $dataColPos . '"]');
        $I->see('Content', '#element-tt_content-802 [data-colpos="' . $dataColPos . '"]');
    }
}
