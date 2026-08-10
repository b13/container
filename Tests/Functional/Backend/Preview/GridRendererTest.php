<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Backend\Preview;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Preview\GridRenderer;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Drawing\DrawingConfiguration;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class GridRendererTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/container/Build/sites' => 'typo3conf/sites',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
            ->createFromUserPreferences($GLOBALS['BE_USER']);
    }

    #[Test]
    public function headingLevelFollowsContainerNesting(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/NestedContainers.csv');
        $gridRenderer = $this->get(GridRenderer::class);
        $context = $this->buildContext();

        // container-1 contains a nested container: own columns h3, nested columns h4
        $first = $gridRenderer->renderGrid(BackendUtility::getRecord('tt_content', 1), $context);
        self::assertSame(['h3', 'h4', 'h4', 'h4'], $this->columnHeadings($first));

        // container-2 is a sibling, not nested, rendered after container-1 - starts at h3
        // again, so the depth counter did not leak from the previous renderGrid() call
        $second = $gridRenderer->renderGrid(BackendUtility::getRecord('tt_content', 3), $context);
        self::assertSame(['h3'], $this->columnHeadings($second));
    }

    /**
     * Extracts the heading level ("h3", "h4", ... or "strong" for the too-deep fallback)
     * of every column title in a rendered grid, in document order. Matches the exact shape
     * ColumnHeader.html renders it in:
     * <h{columnHeaderLevel} id="{columnIdentifier}" class="t3-page-column-title">{column.title}</h{columnHeaderLevel}>
     *
     * @return string[]
     */
    protected function columnHeadings(string $html): array
    {
        preg_match_all('#<(h[1-6]|strong) id="[^"]*" class="t3-page-column-title"#', $html, $matches);
        return $matches[1];
    }

    protected function buildContext(): PageLayoutContext
    {
        $drawingConfiguration = $this->getMockBuilder(DrawingConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $drawingConfiguration->method('isLanguageComparisonMode')->willReturn(false);

        $siteLanguage = new SiteLanguage(0, 'en_US.UTF-8', new \TYPO3\CMS\Core\Http\Uri('/'), []);

        $context = $this->getMockBuilder(PageLayoutContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->method('getDrawingConfiguration')->willReturn($drawingConfiguration);
        $context->method('getPageId')->willReturn(1);
        $context->method('getSiteLanguage')->willReturn($siteLanguage);
        // needed for rendering the child content elements' preview headers, which build an
        // edit-trigger link via $context->getCurrentRequest()->getAttribute('normalizedParams')
        $request = (new ServerRequest())->withAttribute('normalizedParams', NormalizedParams::createFromServerParams([]));
        $context->method('getCurrentRequest')->willReturn($request);

        return $context;
    }
}
