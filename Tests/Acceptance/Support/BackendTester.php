<?php

declare(strict_types=1);

namespace B13\Container\Tests\Acceptance\Support;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Acceptance\Support\_generated\BackendTesterActions;
use Codeception\Util\Locator;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Acceptance\Step\FrameSteps;

class BackendTester extends \Codeception\Actor
{
    use BackendTesterActions;
    use FrameSteps;

    public function getTypo3MajorVersion(): int
    {
        return (new Typo3Version())->getMajorVersion();
    }

    public function loginAs(string $username): void
    {
        $I = $this;
        if ($I->loadSessionSnapshot($username . 'Login')) {
            $I->amOnPage('/typo3');
        } else {
            $I->amOnPage('/typo3');
            $I->waitForElement('body[data-typo3-login-ready]');
            // logging in
            $I->amOnPage('/typo3');
            $I->submitForm('#typo3-login-form', [
                'username' => $username,
                'p_field' => 'password',
            ]);
            $I->saveSessionSnapshot($username . 'Login');
        }
        $I->waitForElement('iframe[name="list_frame"]');
        $I->switchToIFrame('list_frame');
        $I->waitForElement(Locator::firstElement('div.module'));
        $I->switchToIFrame();
    }

    /**
     * v14: Click a content element in the page module to open it in the context panel,
     * then switch into the context panel iframe.
     */
    public function openRecordInContextPanelOrWithEditDocumentController(int $uid): void
    {
        if ($this->getTypo3MajorVersion() < 14 || (new Typo3Version())->getBranch() === '14.1') {
            $this->waitForElement('#element-tt_content-' . $uid . ' a[title="Edit"]');
            $this->click('#element-tt_content-' . $uid . ' a[title="Edit"]');
        } else {
            $this->waitForElement('#element-tt_content-' . $uid . ' typo3-backend-contextual-record-edit-trigger');
            $this->click('#element-tt_content-' . $uid . ' typo3-backend-contextual-record-edit-trigger');
            $this->switchToMainFrame();
            $this->waitForElement('iframe[name="modal_frame"]', 10);
            $this->switchToIFrame('modal_frame');
            $this->waitForElementNotVisible('#t3js-ui-block');
            $this->click('a.t3js-contextual-fullscreen');
            $this->switchToMainFrame();
            $this->switchToContentFrame();
        }
        $this->waitForElement('#EditDocumentController');
    }

    public function getDataColPos(int $containerId, int $colPos): string
    {
        return (string)$colPos;
    }

    public function clickLayoutModuleButton(): void
    {
        if ($this->getTypo3MajorVersion() < 14) {
            $this->click('Page');
        } else {
            $this->click('Layout');
        }
    }

    public function clickNewContentElement(string $colPosSelector): void
    {
        $this->waitForElement($colPosSelector);
        $this->click($colPosSelector . ' typo3-backend-new-content-element-wizard-button');
    }

    public function getNewContentElementLabel(): string
    {
        return 'Create new content';
    }

    public function getNewRecordWizardSelector(): string
    {
        return 'typo3-backend-new-record-wizard';
    }

    public function waitForModal(): void
    {
        if ($this->getTypo3MajorVersion() < 14) {
            $this->waitForElement('.modal-dialog');
        } else {
            $this->waitForElement('dialog.t3js-modal');
        }
    }

    public function selectGermanInLanguageMenu(): void
    {
        if ($this->getTypo3MajorVersion() < 14) {
            $this->waitForText('Language');
            $this->click('Language');
            $this->waitForText('german');
            $this->click('german');
        } else {
            $this->waitForText('english');
            //$this->click('english');
            if ((new Typo3Version())->getBranch() === '14.1') {
                $this->click('.module-docheader-bar-column button');
            } else {
                $this->click('.module-docheader-column button.dropdown-toggle');
            }

            $this->waitForText('german');
            $this->click('german');
        }
    }

    public function selectLanguageComparisonMode(): void
    {
        if ($this->getTypo3MajorVersion() < 14) {
            $this->waitForElement('select[name="actionMenu"]');
            $this->selectOption('select[name="actionMenu"]', 'Language Comparison');
        } else {
            $this->waitForElementVisible('.module-docheader-buttons .btn-group button.dropdown-toggle');
            $this->click('.module-docheader-buttons .btn-group button.dropdown-toggle');
            $this->waitForElementVisible('.module-docheader-buttons .dropdown-menu');
            $this->click('Language Comparison', '.module-docheader-buttons .dropdown-menu');
        }
    }
}
