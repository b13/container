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

    public function getDataColPos(int $containerId, int $colPos): string
    {
        if ($this->getTypo3MajorVersion() > 11) {
            return (string)$colPos;
        }
        return (string)($containerId . '-' . $colPos);
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
        if ($this->getTypo3MajorVersion() < 12) {
            $this->click('Content', $colPosSelector);
            return;
        }
        $this->click($colPosSelector . ' typo3-backend-new-content-element-wizard-button');
    }

    public function getNewContentElementLabel(): string
    {
        if ($this->getTypo3MajorVersion() < 12) {
            return 'Content';
        }
        return 'Create new content';
    }

    public function getNewRecordWizardSelector(): string
    {
        if ($this->getTypo3MajorVersion() < 13) {
            return 'typo3-backend-new-content-element-wizard';
        }
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
        if ($this->getTypo3MajorVersion() < 12) {
            $this->waitForElement('select[name="languageMenu"]');
            $this->selectOption('select[name="languageMenu"]', 'german');
        } elseif ($this->getTypo3MajorVersion() < 14) {
            $this->waitForText('Language');
            $this->click('Language');
            $this->waitForText('german');
            $this->click('german');
        } else {
            $this->waitForText('english');
            //$this->click('english');
            $this->click('.module-docheader-bar-column button');
            $this->waitForText('german');
            $this->click('german');
        }
    }

    public function selectLanguageComparisonMode(): void
    {
        if ($this->getTypo3MajorVersion() < 12) {
            $this->waitForElement('select[name="actionMenu"]');
            $this->selectOption('select[name="actionMenu"]', 'Languages');
        } elseif ($this->getTypo3MajorVersion() < 14) {
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
