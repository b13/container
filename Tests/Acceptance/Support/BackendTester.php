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
use TYPO3\TestingFramework\Core\Acceptance\Step\FrameSteps;

class BackendTester extends \Codeception\Actor
{
    use BackendTesterActions;
    use FrameSteps;

    public function loginAs(string $username): void
    {
        $I = $this;
        if ($I->loadSessionSnapshot($username . 'Login')) {
            $I->amOnPage('/typo3/index.php');
        } else {
            $I->amOnPage('/typo3/index.php');
            $I->waitForElement('body[data-typo3-login-ready]');
            // logging in
            $I->amOnPage('/typo3/index.php');
            $I->submitForm('#typo3-login-form', [
                'username' => $username,
                'p_field' => 'password',
            ]);
            $I->saveSessionSnapshot($username . 'Login');
        }
        $I->switchToIFrame('list_frame');
        $I->waitForElement(Locator::firstElement('div.module'));
        $I->switchToIFrame();
    }
}
