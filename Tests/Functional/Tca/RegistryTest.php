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

use B13\Container\Backend\Grid\ContainerGridColumn;
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
    public function colPosContainerParentCannotBeUsedinColPos(): void
    {
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            self::markTestSkipped('Exception will be thrown on next major release');
        }
        $this->expectException(\InvalidArgumentException::class);
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class)->configureContainer(
            (
            new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [
                    [['name' => 'foo', 'colPos' => ContainerGridColumn::CONTAINER_COL_POS_DELIMITER_V12]],
                ] // grid configuration
            )
            )
        );
    }

    /**
     * @test
     */
    public function getPageTsAddsPreviewConfigEvenIfRegisterInNewContentElementWizardIsSetToFalse(): void
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class)->configureContainer(
            (
            new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [] // grid configuration
            )
            )
            ->setRegisterInNewContentElementWizard(false)
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
    public function originalPageTsIsNotOverriden(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Tca/Fixtures/original_page_ts_is_not_overridden.csv');
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
