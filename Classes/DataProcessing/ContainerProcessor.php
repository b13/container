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
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

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

        $colPos = (int)$cObj->stdWrapValue('colPos', $processorConfiguration);
        if (empty($colPos)) {
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
            $as = $cObj->stdWrapValue('as', $processorConfiguration, 'children');
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

        $contentRecordRenderer = $cObj->getContentObject('RECORDS');
        if ($contentRecordRenderer === null) {
            throw new ContainerDataProcessingFailedException('RECORDS content object not available.', 1691483526);
        }

        $conf = [
            'tables' => 'tt_content',
        ];
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 11) {
            /** @var LanguageAspect $languageAspect */
            $languageAspect = $GLOBALS['TSFE']->getContext()->getAspect('language');
        } else {
            /** @var LanguageAspect $languageAspect */
            $languageAspect = $cObj->getTypoScriptFrontendController()->getContext()->getAspect('language');
        }
        foreach ($children as &$child) {
            if (!isset($processorConfiguration['skipRenderingChildContent']) || (int)$processorConfiguration['skipRenderingChildContent'] === 0) {
                if ($child['l18n_parent'] > 0 && $languageAspect->doOverlays()) {
                    $conf['source'] = $child['l18n_parent'];
                } else {
                    $conf['source'] = $child['uid'];
                }
                if ($child['t3ver_oid'] > 0) {
                    $conf['source'] = $child['t3ver_oid'];
                }
                $child['renderedContent'] = $cObj->render($contentRecordRenderer, $conf);
            }
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
