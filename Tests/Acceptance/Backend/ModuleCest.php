<?php
declare(strict_types = 1);
namespace B13\Container\Tests\Acceptance\Backend;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use B13\Container\Tests\Acceptance\Support\BackendTester;
use B13\Container\Tests\Acceptance\Support\PageTree;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;

/**
 * Tests the styleguide backend module can be loaded
 */
class ModuleCest
{

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
    }

    /**
     * @param BackendTester $I
     */
    public function canCreateContainerContentElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['page-1']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Container');
        $I->click('2 Column Container With Header');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSee('header', '.t3-grid-container');
        $I->canSee('left side', '.t3-grid-container');
        $I->canSee('right side', '.t3-grid-container');
    }

    /**
     * @param BackendTester $I
     */
    public function canCreateContentElementInContainer(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        /*
        $I->click('Page');
        $pageTree->openPath(['page-1']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->click('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        */


        $I->click('Page');
        $pageTree->openPath(['page-1']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->click('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Header Only');
        $I->switchToContentFrame();

        $fieldLabel = 'Column';

        $formSection = $this->getFormSectionByFieldLabel($I, $fieldLabel);
        $inputField = $this->getInputField($formSection);

        #$initializedInputFieldXpath = '(//label[contains(text(),"' . $fieldLabel . '")])'
        #    . '[1]/parent::*//*/input[@data-formengine-input-name][@data-formengine-input-initialized]';

        $I->seeOptionIsSelected($inputField, 'header [200]');

    }


    /**
     * Return the visible input field of element in question.
     *
     * @param $formSection
     * @return RemoteWebElement
     */
    protected function getInputField(RemoteWebElement $formSection)
    {
        return $formSection->findElement(\WebDriverBy::xpath('.//*/input[@data-formengine-input-name]'));
    }


    /**
     * Find this element in form.
     *
     * @param BackendTester $I
     * @param string $fieldLabel
     * @return RemoteWebElement
     */
    protected function getFormSectionByFieldLabel(BackendTester $I, string $fieldLabel)
    {
        $I->comment('Get context for field "' . $fieldLabel . '"');
        return $I->executeInSelenium(
            function (RemoteWebDriver $webDriver) use ($fieldLabel) {
                return $webDriver->findElement(
                    \WebDriverBy::xpath(
                        '(//label[contains(text(),"' . $fieldLabel . '")])[1]/ancestor::fieldset[@class="form-section"][1]'
                    )
                );
            }
        );
    }



}
