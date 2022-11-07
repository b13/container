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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
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

    /**
     * @var Container
     */
    protected $container;

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
            $this->container = $this->containerFactory->buildContainer($contentId);
        } catch (Exception $e) {
            // do nothing
            return $processedData;
        }

        if (empty($processorConfiguration['colPos']) && empty($processorConfiguration['colPos.'])) {
            $allColPos = $this->container->getChildrenColPos();
            foreach ($allColPos as $colPos) {
                $processedData = $this->processColPos(
                    $cObj,
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
            $as = $cObj->stdWrapValue('as', $processorConfiguration, 'children');
            $processedData = $this->processColPos(
                $cObj,
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
        int $colPos,
        string $as,
        array $processedData,
        array $processorConfiguration
    ): array {
        $children = $this->container->getChildrenByColPos($colPos);

        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 12) {
            $contentRecordRenderer = new RecordsContentObject($cObj);
        } else {
            $contentRecordRenderer = new RecordsContentObject();
            $contentRecordRenderer->setContentObjectRenderer($cObj);
            $contentRecordRenderer->setRequest($this->getRequest());
        }
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

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
