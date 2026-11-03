<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Hooks\Datahandler\DatahandlerProcess;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyToLanguageSortingTest extends AbstractContentDefender
{
    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            $this->testExtensionsToLoad[] = 'typo3conf/ext/content_defender';
        }
        parent::setUp();
    }

    #[Test]
    #[Group('content_defender')]
    public function localizeWithMultipleNestedElements(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeWithMultipleNestedElements.csv');
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copyToLanguage' => 4,
                ],
            ],
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyToLanguageSorting/LocalizeWithMultipleNestedElementsResult.csv');
        self::assertFalse((GeneralUtility::makeInstance(DatahandlerProcess::class))->areContentElementRestrictionsLooked());
    }
}
