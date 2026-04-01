<?php

declare(strict_types=1);

namespace B13\Container\DataProcessing;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use function array_map;
use B13\Container\Domain\Factory\FrontendContainerFactory;
use B13\Container\Tca\Registry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\ContentArea;
use TYPO3\CMS\Core\Page\ContentAreaClosure;
use TYPO3\CMS\Core\Page\ContentAreaCollection;
use TYPO3\CMS\Core\Page\ContentSlideMode;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Automatically detects if content element has container columns
 * adds them lazily to the content variable.
 * The ContentArea can be used in f:render.contentArea ViewHelper
 *
 * Only use this DataProcessor for TYPO3 v14 or higher:
 *
 * typoscript:
 *     lib.contentElement.dataProcessing.1773665522 = B13\Container\DataProcessing\ContentAreaProcessor
 *     #or
 *     tt_content.b13-2cols < lib.contentElement
 *     tt_content.b13-2cols {
 *         templateName = 2Cols
 *         templateRootPaths.10 = EXT:base/Resources/Private/Templates
 *         dataProcessing.100 = B13\Container\DataProcessing\ContentAreaProcessor
 *     }
 *
 * html:
 *     <f:render.contentArea contentArea="{content.200}" />
 */
#[Autoconfigure(public: true)]
readonly class ContentAreaProcessor implements DataProcessorInterface
{
    public function __construct(
        protected ContentDataProcessor $contentDataProcessor,
        protected Context $context,
        protected FrontendContainerFactory $frontendContainerFactory,
        protected Registry $tcaRegistry,
        protected RecordFactory $recordFactory,
        protected Typo3Version $typo3Version,
        protected LoggerInterface $logger,
    ) {
    }

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        if (((float)$this->typo3Version->getBranch()) <= 14.1) {
            $this->logger->error(ContentAreaProcessor::class . ' requires TYPO3 v14.2 or higher. Please check your configuration.');

            return $processedData;
        }

        $record = $cObj->data;

        $CType = $record['CType'] ?? '';
        if (!$this->tcaRegistry->isContainerElement($CType)) {
            return $processedData;
        }

        $columnsColPos = $this->tcaRegistry->getAllAvailableColumnsColPos($CType);

        $container = null;

        $areas = [];
        foreach ($columnsColPos as $colPos) {
            $areas[$colPos] = new ContentAreaClosure(
                function () use (&$container, $CType, $cObj, $record, $colPos): ContentArea {
                    $container ??= $this->frontendContainerFactory->buildContainer($cObj, $this->context, (int)$record['uid']);

                    $contentDefenderConfiguration = $this->tcaRegistry->getContentDefenderConfiguration($CType, $colPos);

                    $rows = $container->getChildrenByColPos($colPos);

                    $records = array_map(fn ($row) => $this->recordFactory->createFromDatabaseRow('tt_content', $row), $rows);
                    return new ContentArea(
                        (string)$colPos,
                        $this->tcaRegistry->getColPosName($record['CType'], $colPos),
                        $colPos,
                        ContentSlideMode::None,
                        GeneralUtility::trimExplode(',', $contentDefenderConfiguration['allowedContentTypes'] ?? '', true),
                        GeneralUtility::trimExplode(',', $contentDefenderConfiguration['disallowedContentTypes'] ?? '', true),
                        [
                            'container' => $container,
                        ],
                        $records,
                    );
                },
            );
        }

        $processedData[$processorConfiguration['as'] ?? 'content'] = new ContentAreaCollection($areas);
        return $processedData;
    }
}
