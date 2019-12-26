<?php

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * Contains functional tests for the XmlSitemap Index
 */
class DefaultLanguageTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = ['core', 'frontend', 'workspaces'];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:container/Tests/Functional/Fixtures/TypoScript/constants.typoscript'],
                'setup' => ['EXT:container/Tests/Functional/Fixtures/TypoScript/setup.typoscript']
            ]
        );
    }

    /**
     * @atestb
     */
    public function checkIfSiteMapIndexContainsPagesSitemap(): void
    {
        $response = $this->executeFrontendRequest(
            (new InternalRequest('http://localhost/'))
        );

        self::assertEquals(200, $response->getStatusCode());
        #self::assertArrayHasKey('Content-Length', $response->getHeaders());
        #self::assertGreaterThan(0, $response->getHeader('Content-Length')[0]);
        #self::assertArrayHasKey('Content-Type', $response->getHeaders());
        #self::assertEquals('application/xml;charset=utf-8', $response->getHeader('Content-Type')[0]);
        #self::assertArrayHasKey('X-Robots-Tag', $response->getHeaders());
        #self::assertEquals('noindex', $response->getHeader('X-Robots-Tag')[0]);
        #self::assertRegExp('/<loc>http:\/\/localhost\/\?sitemap=pages&amp;type=1533906435&amp;cHash=[^<]+<\/loc>/', (string)$response->getBody());
    }
}
