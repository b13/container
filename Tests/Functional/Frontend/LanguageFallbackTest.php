<?php

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;


/**
 * Contains functional tests for the XmlSitemap Index
 */
class LanguageFallbackTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = ['core', 'frontend', 'workspaces', 'fluid_styled_content'];

    /**
     * @var string[]
     */
    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/container/Build/sites' => 'typo3conf/sites',
    ];

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
     * @test
     */
    public function nothingTranslated(): void
    {
        $response = $this->executeFrontendRequest(new InternalRequest('/fr'));
        $body = (string)$response->getBody();
        $this->assertStringContainsString('<h1 class="container">container-default</h1>', $body, 'container-default heading not found');
        $this->assertStringContainsString('<h6 class="header-childs">header-default</h6>', $body, 'header-default heading not found');
    }

    /**
     * @test
     */
    public function bothTranslated(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/LanguageFallback/tt_content_both_translated.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/fr'));
        $body = (string)$response->getBody();
        $this->assertStringContainsString('<h1 class="container">container-fr</h1>', $body, 'container-fr heading not found');
        $this->assertStringContainsString('<h6 class="header-childs">header-fr</h6>', $body, 'header-fr heading not found');
    }

    /**
     * @test
     */
    public function childTranslated(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/LanguageFallback/tt_content_child_translated.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/fr'));
        $body = (string)$response->getBody();
        $this->assertStringContainsString('<h1 class="container">container-default</h1>', $body, 'container-default heading not found');
        $this->assertStringContainsString('<h6 class="header-childs">header-fr</h6>', $body, 'header-fr heading not found');
    }

    /**
     * @test
     */
    public function containerTranslated(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/LanguageFallback/tt_content_container_translated.xml');
        $response = $this->executeFrontendRequest(new InternalRequest('/fr'));
        $body = (string)$response->getBody();
        $this->assertStringContainsString('<h1 class="container">container-fr</h1>', $body, 'container-fr heading not found');
        $this->assertStringContainsString('<h6 class="header-childs">header-default</h6>', $body, 'header-default heading not found');
    }
}
