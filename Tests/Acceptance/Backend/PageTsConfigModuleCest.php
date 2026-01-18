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
use Codeception\Scenario;
use TYPO3\CMS\Core\Information\Typo3Version;

class PageTsConfigModuleCest
{
    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');
    }

    public function canSeeContainerPageTsConfig(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13, Scenario $scenario)
    {
        if ($I->getTypo3MajorVersion() < 12) {
            $scenario->skip('InfoModuleCest is used');
        }
        $I->click('Page TSconfig');
        if ($I->getTypo3MajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['home', 'pageWithContainer-6']);
        } else {
            $pageTreeV13->openPath(['home', 'pageWithContainer-6']);
        }
        $I->wait(0.2);
        $I->switchToContentFrame();

        if ($I->getTypo3MajorVersion() < 14) {
            $I->waitForElement('select[name="moduleMenu"]');
            $I->selectOption('select[name="moduleMenu"]', 'Active page TSconfig');
            $I->waitForElement('input[name="searchValue"]');
        } else {
            $I->waitForElementVisible('.module-docheader-buttons .btn-group button.dropdown-toggle');
            $I->click('.module-docheader-buttons .btn-group button.dropdown-toggle');
            $I->waitForElementVisible('.module-docheader-buttons .dropdown-menu');
            $I->click('Active page TSconfig', '.module-docheader-buttons .dropdown-menu');
        }
        if ($I->getTypo3MajorVersion() < 13) {
            $I->fillField('searchValue', 'b13-2cols-with-header-container');
        } else {
            $I->fillField('searchValue', 'b13-1col');
        }
        $I->waitForText('Configuration');
        $I->click('Configuration');
        if ($I->getTypo3MajorVersion() > 12) {
            $I->waitForText('b13-1col');
            $I->dontSee('show = b13-2cols-with-header-container');
            $I->see('removeItems = b13-1col');
        } else {
            $I->waitForText('b13-2cols-with-header-container');
            $I->see('show = b13-2cols-with-header-container');
            $I->dontSee('removeItems = b13-1col');
        }
    }
}
