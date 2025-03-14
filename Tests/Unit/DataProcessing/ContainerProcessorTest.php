<?php

declare(strict_types=1);

namespace B13\Container\Tests\Unit\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\DataProcessing\ContainerProcessor;
use B13\Container\Domain\Factory\FrontendContainerFactory;
use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ContainerProcessorTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @test
     */
    public function configuredContentIdIsUsed(): void
    {
        $processorConfiguration = ['contentId' => 1];
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
        $contentObjectRenderer->expects($this->never())->method('stdWrap');
        $context = $this->getMockBuilder(Context::class)->getMock();
        $contentDataProcessor = $this->getMockBuilder(ContentDataProcessor::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory = $this->getMockBuilder(FrontendContainerFactory::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory->expects($this->once())->method('buildContainer')->with(
            $contentObjectRenderer, $context, 1
        )->willReturn(new Container([], []));
        $containerProcessor = new ContainerProcessor($contentDataProcessor, $context, $frontendContainerFactory);
        $containerProcessor->process($contentObjectRenderer, [], $processorConfiguration, []);
    }

    /**
     * @test
     */
    public function configuredContentIdStdWrapIsUsed(): void
    {
        $processorConfiguration = ['contentId' => 1, 'contentId.' => 'foo'];
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
        $contentObjectRenderer->expects($this->once())->method('stdWrap')->with(1, 'foo')->willReturn(2);
        $context = $this->getMockBuilder(Context::class)->getMock();
        $contentDataProcessor = $this->getMockBuilder(ContentDataProcessor::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory = $this->getMockBuilder(FrontendContainerFactory::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory->expects($this->once())->method('buildContainer')->with(
            $contentObjectRenderer, $context, 2
        )->willReturn(new Container([], []));
        $containerProcessor = new ContainerProcessor($contentDataProcessor, $context, $frontendContainerFactory);
        $containerProcessor->process($contentObjectRenderer, [], $processorConfiguration, []);
    }

    /**
     * @test
     */
    public function canBeCalledWithoutContentId(): void
    {
        $processorConfiguration = ['contentId.' => 'foo'];
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
        $contentObjectRenderer->expects($this->once())->method('stdWrap')->with('', 'foo')->willReturn(2);
        $context = $this->getMockBuilder(Context::class)->getMock();
        $contentDataProcessor = $this->getMockBuilder(ContentDataProcessor::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory = $this->getMockBuilder(FrontendContainerFactory::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory->expects($this->once())->method('buildContainer')->with(
            $contentObjectRenderer, $context, 2
        )->willReturn(new Container([], []));
        $containerProcessor = new ContainerProcessor($contentDataProcessor, $context, $frontendContainerFactory);
        $containerProcessor->process($contentObjectRenderer, [], $processorConfiguration, []);
    }

    /**
     * @test
     */
    public function nullIsUsedForFactoryIfNoContentIdIsGiven(): void
    {
        $processorConfiguration = [];
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
        $contentObjectRenderer->expects($this->never())->method('stdWrap');
        $context = $this->getMockBuilder(Context::class)->getMock();
        $contentDataProcessor = $this->getMockBuilder(ContentDataProcessor::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory = $this->getMockBuilder(FrontendContainerFactory::class)->disableOriginalConstructor()->getMock();
        $frontendContainerFactory->expects($this->once())->method('buildContainer')->with(
            $contentObjectRenderer, $context, null
        )->willReturn(new Container([], []));
        $containerProcessor = new ContainerProcessor($contentDataProcessor, $context, $frontendContainerFactory);
        $containerProcessor->process($contentObjectRenderer, [], $processorConfiguration, []);
    }

}
