<?php

namespace B13\Container\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{
    /**
     * @var RecordLocalizeSummaryModifier
     */
    protected $recordLocalizeSummaryModifier;

    public function __construct(RecordLocalizeSummaryModifier $recordLocalizeSummaryModifier = null)
    {
        parent::__construct();
        if ($recordLocalizeSummaryModifier === null) {
            $recordLocalizeSummaryModifier = GeneralUtility::makeInstance(RecordLocalizeSummaryModifier::class);
        }
        $this->recordLocalizeSummaryModifier = $recordLocalizeSummaryModifier;
    }

    public function getRecordLocalizeSummary(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();
        if (!isset($params['pageId'], $params['colPos'], $params['destLanguageId'], $params['languageId'])) {
            $response = $response->withStatus(500);
            return $response;
        }

        $records = [];
        $databaseConnection = $this->getDatabaseConnection();
        $res = $this->localizationRepository->getRecordsToCopyDatabaseResult($params['pageId'], $params['colPos'], $params['destLanguageId'], $params['languageId'], '*');
        while ($row = $databaseConnection->sql_fetch_assoc($res)) {
            $records[] = [
                'icon' => $this->iconFactory->getIconForRecord('tt_content', $row, Icon::SIZE_SMALL)->render(),
                'title' => $row[$GLOBALS['TCA']['tt_content']['ctrl']['label']],
                'uid' => $row['uid']
            ];
        }
        $databaseConnection->sql_free_result($res);
        $records = $this->recordLocalizeSummaryModifier->rebuildPayload($records);
        $response->getBody()->write(json_encode($records));
        return $response;
    }
}
