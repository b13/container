<?php

namespace B13\Container\DataProcessing;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use B13\Container\Database;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class ContainerProcessor implements DataProcessorInterface
{
    /**
     * @var Database
     */
    protected $database = null;

    /**
     * ContainerLayoutView constructor.
     * @param Database $database
     */
    public function __construct(Database $database = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
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

        $childs = $this->database->fetchRecordsByParentAndColPos($contentId, $colPos);

        if ($processorConfiguration['as']) {
            $processedData[$processorConfiguration['as']] = $childs;
        } else {
            $processedData['childs'] = $childs;
        }

        return $processedData;
    }
}
