<?php

namespace B13\Container\Tests\Functional\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Integrity\Database;
use B13\Container\Integrity\Sorting;
use B13\Container\Tca\Registry;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class FixturesTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    protected Sorting $sorting;

    /**
     * @var non-empty-string[]
     */
    protected array $coreExtensionsToLoad = ['workspaces'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
        $context = GeneralUtility::makeInstance(Context::class);
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $sortingDatabase = GeneralUtility::makeInstance(Database::class);
        $factoryDatabase = GeneralUtility::makeInstance(\B13\Container\Domain\Factory\Database::class, $context);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class, $factoryDatabase, $containerRegistry, $context);
        $containerService = GeneralUtility::makeInstance(ContainerService::class, $containerRegistry, $containerFactory);
        $this->sorting = GeneralUtility::makeInstance(Sorting::class, $sortingDatabase, $containerRegistry, $containerFactory, $containerService);
    }

    /**
     * @return array
     */
    public static function csvProvider(): array
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/')->name('*.csv');
        $results = [];
        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());
            if (str_contains($content, 'sorting') == false) {
                continue;
            }
            $results[] = ['csv' => str_replace(__DIR__ . '/', '', $file->getRealPath())];
        }
        return $results;
    }

    /**
     * @test
     * @dataProvider csvProvider
     * @group fixtures
     */
    public function fixturesSorting(string $csv): void
    {
        $this->importCSVDataSet(__DIR__ . '/' . $csv);
        $errors = $this->sorting->run(true);
        self::assertTrue(count($errors) === 0, 'got errors for ' . $csv);
    }
}
