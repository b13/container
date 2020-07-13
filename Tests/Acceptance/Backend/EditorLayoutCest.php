<?php

declare(strict_types = 1);
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
        $I->useExistingSession('editor');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canSeeNewContentButton(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->see('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
    }
}
