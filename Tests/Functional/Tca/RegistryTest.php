<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RegistryTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
    ];

    /**
     * @test
     */
    public function getPageTsAddsPreviewConfigEvenIfRegisterInNewContentElementWizardIsSetToFalse(): void
    {
        // https://github.com/b13/container/pull/153
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class)->configureContainer(
            (new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [] // grid configuration
            ))->setRegisterInNewContentElementWizard(false)
            ->setBackendTemplate('EXT:container/Resources/Private/Templates/Container.html')
        );
        $registry = GeneralUtility::makeInstance(Registry::class);
        $pageTs = $registry->getPageTsString();
        $expected = 'mod.web_layout.tt_content.preview {
b13-container = EXT:container/Resources/Private/Templates/Container.html
}';
        self::assertStringContainsString($expected, $pageTs);
    }

    /**
     * @test
     */
    public function getPageTsStringReturnsGroupAsGroupLabelWhenGroupIsNotAddetToItemGroups(): void
    {
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            // s. https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-102834-RemoveItemsFromNewContentElementWizard.html
            self::markTestSkipped('new content element wizards removed');
        }
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class)->configureContainer(
            (
            new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [] // grid configuration
            )
            )->setGroup('baz')
        );
        $registry = GeneralUtility::makeInstance(Registry::class);
        $pageTs = $registry->getPageTsString();
        $expected = 'mod.wizards.newContentElement.wizardItems.baz.header = baz';
        self::assertStringContainsString($expected, $pageTs);
    }

    /**
     * @test
     */
    public function tcaDefaultGroupIsAddedToNewContentElementCommonGroup(): void
    {
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            // s. https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-102834-RemoveItemsFromNewContentElementWizard.html
            self::markTestSkipped('new content element wizards removed');
        }
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class)->configureContainer(
            (
            new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [] // grid configuration
            )
            )->setGroup('default')
        );
        $registry = GeneralUtility::makeInstance(Registry::class);
        $pageTs = $registry->getPageTsString();
        $expected = 'mod.wizards.newContentElement.wizardItems.common.show := addToList(b13-container)';
        self::assertStringContainsString($expected, $pageTs);
    }

    /**
     * @test
     */
    public function originalPageTsIsNotOverriden(): void
    {
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            // s. https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/13.0/Breaking-102834-RemoveItemsFromNewContentElementWizard.html
            self::markTestSkipped('new content element wizards removed');
        }
        $this->importCSVDataSet(__DIR__ . '/Fixtures/original_page_ts_is_not_overridden.csv');
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class)->configureContainer(
            (
            new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [] // grid configuration
            )
            )->setGroup('special')
        );
        $pageTsConfig = BackendUtility::getPagesTSconfig(1);
        $specialHeader = $pageTsConfig['mod.']['wizards.']['newContentElement.']['wizardItems.']['special.']['header'] ?? '';
        $expected = 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special';
        self::assertSame($expected, $specialHeader);
    }
}
