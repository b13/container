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

use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Factory\PageView\Frontend\ContainerFactory;
use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;

class ContainerProcessor implements DataProcessorInterface
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @var ContentDataProcessor
     */
    protected $contentDataProcessor;

    public function __construct(ContainerFactory $containerFactory, ContentDataProcessor $contentDataProcessor)
    {
        $this->containerFactory = $containerFactory;
        $this->contentDataProcessor = $contentDataProcessor;
    }

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }
        if ($processorConfiguration['contentId.'] ?? false) {
            $contentId = (int)$cObj->stdWrap($processorConfiguration['contentId'], $processorConfiguration['contentId.']);
        } elseif ($processorConfiguration['contentId'] ?? false) {
            $contentId = (int)$processorConfiguration['contentId'];
        } else {
            $contentId = (int)$cObj->data['uid'];
        }

        try {
            $container = $this->containerFactory->buildContainer($contentId);
        } catch (Exception $e) {
            // do nothing
            return $processedData;
        }

        if (empty($processorConfiguration['colPos']) && empty($processorConfiguration['colPos.'])) {
            $allColPos = $container->getChildrenColPos();
            foreach ($allColPos as $colPos) {
                $processedData = $this->processColPos(
                    $cObj,
                    $container,
                    $colPos,
                    'children_' . $colPos,
                    $processedData,
                    $processorConfiguration
                );
            }
        } else {
            if ($processorConfiguration['colPos.'] ?? null) {
                $colPos = (int)$cObj->stdWrap($processorConfiguration['colPos'], $processorConfiguration['colPos.']);
            } else {
                $colPos = (int)$processorConfiguration['colPos'];
            }
            $as = 'children';
            if ($processorConfiguration['as']) {
                $as = $processorConfiguration['as'];
            }
            $processedData = $this->processColPos(
                $cObj,
                $container,
                $colPos,
                $as,
                $processedData,
                $processorConfiguration
            );
        }
        return $processedData;
    }

    protected function processColPos(
        ContentObjectRenderer $cObj,
        Container $container,
        int $colPos,
        string $as,
        array $processedData,
        array $processorConfiguration
    ): array {
        $children = $container->getChildrenByColPos($colPos);

        $contentRecordRenderer = new RecordsContentObject($cObj);
        $conf = [
            'tables' => 'tt_content',
        ];
        foreach ($children as &$child) {
            if ($child['l18n_parent'] > 0) {
                $conf['source'] = $child['l18n_parent'];
            } else {
                $conf['source'] = $child['uid'];
            }
            if ($child['t3ver_oid'] > 0) {
                $conf['source'] = $child['t3ver_oid'];
            }
            $child['renderedContent'] = $cObj->render($contentRecordRenderer, $conf);
            /** @var ContentObjectRenderer $recordContentObjectRenderer */
            $recordContentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $recordContentObjectRenderer->start($child, 'tt_content');
            $child = $this->contentDataProcessor->process($recordContentObjectRenderer, $processorConfiguration, $child);
        }
        $processedData[$as] = $children;
        return $processedData;
    }
}
