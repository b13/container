<?php

namespace B13\Container\DataProcessing;


use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use B13\Container\Domain\Factory\ContainerFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;


class ContainerProcessor implements DataProcessorInterface
{

    /**
     * @var ContainerFactory
     */
    protected $containerFactory = null;

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
    )
    {

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
        if ($processorConfiguration['colPos.']) {
            $colPos = (int)$cObj->stdWrap($processorConfiguration['colPos'], $processorConfiguration['colPos.']);
        } else {
            $colPos = (int)$processorConfiguration['colPos'];
        }

        try {
            $container = $this->containerFactory->buildContainer($contentId);
            $childs = $container->getChildsByColPos($colPos);

            $contentRecordRenderer = new RecordsContentObject($cObj);
            $conf = [
                'tables' => 'tt_content'
            ];
            foreach ($childs as &$child) {
                $conf['source'] = $child['uid'];
                $child['renderedContent'] = $cObj->render($contentRecordRenderer, $conf);
            }

            if ($processorConfiguration['as']) {
                $processedData[$processorConfiguration['as']] = $childs;
            } else {
                $processedData['childs'] = $childs;
            }
        } catch (Exception $e) {
            // nothing is done
        }

        return $processedData;
    }
}
