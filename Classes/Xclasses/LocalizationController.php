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
use TYPO3\CMS\Core\Http\JsonResponse;
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
        $response = parent::getRecordLocalizeSummary($request, $response);
        $payload = json_decode($response->getBody()->getContents(), true);
        #var_dump($payload);
        #die();
        $payload = $this->recordLocalizeSummaryModifier->rebuildPayload($payload);
        $response->getBody()->write(json_encode($payload));
        return $response;
    }
}
