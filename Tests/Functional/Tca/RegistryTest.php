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
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
            // s. https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/ContentElements/CustomBackendPreview.html#ConfigureCE-Preview-EventListener
            self::markTestSkipped('event listener is used');
        } else {
            // https://github.com/b13/container/pull/153
            self::markTestSkipped('todo check this, TS removed, mod.web_layout.tt_content.preview');
        }
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

    /**
     * @test
     * @dataProvider contentDefenderData
     */
    public function getContentDefenderConfiguration(int $colPos, array $disallowedCTypes, array $expectedConfiguration)
    {
        $registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Registry::class);
        $registry->configureContainer(
            (
            new ContainerConfiguration(
                'b13-container', // CType
                'foo', // label
                'bar', // description
                [
                    [
                        ['name' => 'foo', 'colPos' => 1],
                        ['name' => 'foo2', 'colPos' => 2, 'allowed' => ['CType' => 'ce1'], 'disallowed' => ['CType' => 'ce2']],
                        ['name' => 'foo3', 'colPos' => 3, 'maxitems' => 3],
                        ['name' => 'foo4', 'colPos' => 4, 'allowed' => ['CType' => 'ce1']],
                    ],
                ] // grid configuration
            )
            )->setGroup('special')
        );
        if ($disallowedCTypes !== []) {
            foreach ($disallowedCTypes as $cType) {
                $registry->addDisallowedCType($cType);
            }
        }
        $configuration = $registry->getContentDefenderConfiguration('b13-container', $colPos);
        self::assertEquals($expectedConfiguration, $configuration);
    }

    public static function contentDefenderData(): \Traversable
    {
        yield 'no column' => [
            'colPos' => 999,
            'disallowedCTypes' => [],
            'expectedConfiguration' => [],
        ];
        yield 'default configuration' => [
            'colPos' => 1,
            'disallowedCTypes' => [],
            'expectedConfiguration' => ['allowed.' => [], 'disallowed.' => [], 'maxitems' => 0],
        ];
        yield 'maxitems' => [
            'colPos' => 3,
            'disallowedCTypes' => [],
            'expectedConfiguration' => ['allowed.' => [], 'disallowed.' => [], 'maxitems' => 3],
        ];
        yield 'allowed and disallowed' => [
            'colPos' => 2,
            'disallowedCTypes' => [],
            'expectedConfiguration' => ['allowed.' => ['CType' => 'ce1'], 'disallowed.' => ['CType' => 'ce2'], 'maxitems' => 0],
        ];
        yield 'globally disallowed CType' => [
            'colPos' => 4,
            'disallowedCTypes' => ['ce3'],
            'expectedConfiguration' => ['allowed.' => ['CType' => 'ce1'], 'disallowed.' => ['CType' => 'ce3'], 'maxitems' => 0],
        ];
        yield 'globally disallowed CType explicity allowed' => [
            'colPos' => 4,
            'disallowedCTypes' => ['ce1'],
            'expectedConfiguration' => ['allowed.' => ['CType' => 'ce1'], 'disallowed.' => [], 'maxitems' => 0],
        ];
    }
}
