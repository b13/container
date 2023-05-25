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
use TYPO3\CMS\Core\Information\Typo3Version;

class InfoModuleCest
{
    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');
    }

    public function canSeeContainerPageTsConfig(BackendTester $I, PageTree $pageTree, Scenario $scenario)
    {
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() >= 12) {
            $scenario->skip('PageTsConfigModuleCest is used');
        }
        $I->click('Info');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
        $pageTree->openPath(['home', 'pageWithContainer-6']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $selectbox1Name = 'WebInfoJumpMenu';
        $selectbox2Name = 'SET[tsconf_parts]';

        $I->waitForElement('select[name="' . $selectbox1Name . '"]');
        $I->selectOption('select[name="' . $selectbox1Name . '"]', 'Page TSconfig');
        $I->waitForElement('select[name="' . $selectbox2Name . '"]');
        $I->selectOption('select[name="' . $selectbox2Name . '"]', 99);
        $I->see('b13-2cols-with-header-container = EXT:container/Resources/Private/Templates/Container.html');
    }
}
