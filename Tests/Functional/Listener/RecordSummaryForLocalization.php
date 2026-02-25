<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Controller\Event\AfterRecordSummaryForLocalizationEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RecordSummaryForLocalization extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    #[Test]
    public function childrenIsMovedIntoBackendLayoutColPosIfContainerIsAlreadyTranslated(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('tested by RecordLocalizeSummaryModifierTest Unit Test');
        }
        $records = [
            0 => [
                0 => ['uid' => 41, 'title' => 'first element'],
                1 => ['uid' => 40, 'title' => 'last element'],
            ],
            200 => [
                0 => ['uid' => 4, 'title' => 'ce 2'],
            ],
        ];
        $columns = [0 => 'Normal'];
        $event = new AfterRecordSummaryForLocalizationEvent($records, $columns);
        $this->importCSVDataSet(__DIR__ . '/Fixtures/localize_container_child.csv');
        $listener = $this->getContainer()->get(\B13\Container\Listener\RecordSummaryForLocalization::class);
        $listener($event);
        $records = $event->getRecords();
        $expected = [
            0 => [
                0 => ['uid' => 41, 'title' => 'first element'],
                1 => ['uid' => 4, 'title' => 'ce 2'],
                2 => ['uid' => 40, 'title' => 'last element'],
            ],
        ];
        self::assertSame($expected, $records);
    }

    #[Test]
    public function childrenIsNotMovedIntoBackendLayoutColPosIfContainerShouldBeTranslated(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('tested by RecordLocalizeSummaryModifierTest Unit Test');
        }
        $records = [
            0 => [
                0 => ['uid' => 5, 'title' => 'container'],
            ],
            200 => [
                0 => ['uid' => 4, 'title' => 'ce 2'],
            ],
        ];
        $columns = [0 => 'Normal'];
        $event = new AfterRecordSummaryForLocalizationEvent($records, $columns);
        $this->importCSVDataSet(__DIR__ . '/Fixtures/localize_container.csv');
        $listener = $this->getContainer()->get(\B13\Container\Listener\RecordSummaryForLocalization::class);
        $listener($event);
        $records = $event->getRecords();
        $expected = [
            0 => [
                0 => ['uid' => 5, 'title' => 'container'],
            ],
            200 => [
                0 => ['uid' => 4, 'title' => 'ce 2'],
            ],
        ];
        self::assertSame($expected, $records);
    }
}
