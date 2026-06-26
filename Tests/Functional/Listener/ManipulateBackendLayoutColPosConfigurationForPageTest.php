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

use B13\Container\Listener\ManipulateBackendLayoutColPosConfigurationForPage;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\Event\ManipulateBackendLayoutColPosConfigurationForPageEvent;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ManipulateBackendLayoutColPosConfigurationForPageTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    #[Test]
    public function newElementAtTopOfPage(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('only v14');
        }
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ManipulateBackendLayoutColPosConfigurationForPageTest.csv');
        $request = new ServerRequest();
        $request = $request->withQueryParams(['edit' => ['tt_content' => [1 => 'new']]]);
        $e = new ManipulateBackendLayoutColPosConfigurationForPageEvent([], new BackendLayout('foo', 'bar', []), 0, 1, $request);
        $listener = $this->getContainer()->get(ManipulateBackendLayoutColPosConfigurationForPage::class);
        $listener($e);
        self::assertSame([], $e->configuration);
    }

    #[Test]
    public function newElementAfterContainerChild(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('only v14');
        }
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ManipulateBackendLayoutColPosConfigurationForPageTest.csv');
        $request = new ServerRequest();
        $request = $request->withQueryParams(['edit' => ['tt_content' => [-1 => 'new']]]);
        $e = new ManipulateBackendLayoutColPosConfigurationForPageEvent([], new BackendLayout('foo', 'bar', []), 200, 1, $request);
        $listener = $this->getContainer()->get(ManipulateBackendLayoutColPosConfigurationForPage::class);
        $listener($e);
        self::assertSame(['allowedContentTypes' => 'header, textmedia, b13-2cols', 'disallowedContentTypes' => ''], $e->configuration);
    }

    #[Test]
    public function editContainerChildElement(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('only v14');
        }
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ManipulateBackendLayoutColPosConfigurationForPageTest.csv');
        $request = new ServerRequest();
        $request = $request->withQueryParams(['edit' => ['tt_content' => [1 => 'edit']]]);
        $e = new ManipulateBackendLayoutColPosConfigurationForPageEvent([], new BackendLayout('foo', 'bar', []), 200, 1, $request);
        $listener = $this->getContainer()->get(ManipulateBackendLayoutColPosConfigurationForPage::class);
        $listener($e);
        self::assertSame(['allowedContentTypes' => 'header, textmedia, b13-2cols', 'disallowedContentTypes' => ''], $e->configuration);
    }

    #[Test]
    public function editContainerChildElementWithWrongColPos(): void
    {
        // https://forge.typo3.org/issues/110106
        if ((new Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('only v14');
        }
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ManipulateBackendLayoutColPosConfigurationForPageTest.csv');
        $request = new ServerRequest();
        $request = $request->withQueryParams(['edit' => ['tt_content' => [1 => 'edit']]]);
        $e = new ManipulateBackendLayoutColPosConfigurationForPageEvent([], new BackendLayout('foo', 'bar', []), 0, 1, $request);
        $listener = $this->getContainer()->get(ManipulateBackendLayoutColPosConfigurationForPage::class);
        $listener($e);
        self::assertSame([], $e->configuration);
    }
}
