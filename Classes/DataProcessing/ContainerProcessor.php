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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;

class ContainerProcessor implements DataProcessorInterface
{

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    public function __construct(ContainerFactory $containerFactory = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
    }

    /**
     * @param ContentObjectRenderer $cObj
     * @param array $contentObjectConfiguration
     * @param array $processorConfiguration
     * @param array $processedData
     * @return array
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }
        if ($processorConfiguration['contentId.']) {
            $contentId = (int)$cObj->stdWrap($processorConfiguration['contentId'], $processorConfiguration['contentId.']);
        } elseif ($processorConfiguration['contentId']) {
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
                    $processedData
                );
            }
        } else {
            if ($processorConfiguration['colPos.']) {
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
                $processedData
            );
        }
        return $processedData;
    }

    protected function processColPos(
        ContentObjectRenderer $cObj,
        Container $container,
        int $colPos,
        string $as,
        array $processedData
    ): array {
        $children = $container->getChildrenByColPos($colPos);

        $contentRecordRenderer = new RecordsContentObject($cObj);
        $conf = [
            'tables' => 'tt_content'
        ];
        foreach ($children as &$child) {
            if ($child['t3ver_oid'] > 0) {
                $conf['source'] = $child['t3ver_oid'];
            } else {
                $conf['source'] = $child['uid'];
            }
            $child['renderedContent'] = $cObj->render($contentRecordRenderer, $conf);
        }
        if (strpos($as, '.') !== false) {
            [$topKey, $subKey] = GeneralUtility::trimExplode('.', $as, false, 2);
            if (isset($processedData[$topKey])) {
                $processedData[$topKey][$subKey] = $children;
            } else {
                $processedData[$topKey] = [$subKey => $children];
            }
        } else {
            $processedData[$as] = $children;
        }
        return $processedData;
    }
}
