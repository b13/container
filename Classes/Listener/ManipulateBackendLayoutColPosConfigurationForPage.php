<?php

declare(strict_types=1);

namespace B13\Container\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Tca\Registry;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\ManipulateBackendLayoutColPosConfigurationForPageEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'tx-container-manipulate-backend-layout-col-pos-configuration-for-page')]
class ManipulateBackendLayoutColPosConfigurationForPage
{
    public function __construct(protected ContainerFactory $containerFactory, protected Registry $tcaRegistry)
    {
    }

    public function __invoke(ManipulateBackendLayoutColPosConfigurationForPageEvent $e)
    {
        $parent = $this->getParentUid($e->request);
        if ($parent === null) {
            return;
        }

        try {
            $container = $this->containerFactory->buildContainer($parent);
        } catch (Exception $e) {
            // not a container
            return;
        }
        $cType = $container->getCType();
        $configuration = $this->tcaRegistry->getContentDefenderConfiguration($cType, $e->colPos);
        $e->configuration = [
            'allowedContentTypes' => $configuration['allowedContentTypes'],
            'disallowedContentTypes' => $configuration['disallowedContentTypes'],
        ];
    }

    private function getParentUid(?ServerRequestInterface $request): ?int
    {
        if ($request === null) {
            return null;
        }
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['tx_container_parent']) && $queryParams['tx_container_parent'] > 0) {
            // new content elemment wizard
            return (int)$queryParams['tx_container_parent'];
        }
        if (
            isset($queryParams['defVals']['tt_content']['tx_container_parent']) &&
            $queryParams['defVals']['tt_content']['tx_container_parent'] > 0
        ) {
            // TcaCTypeItems: new record
            return (int)$queryParams['defVals']['tt_content']['tx_container_parent'];
        }
        if (isset($queryParams['edit']['tt_content'])) {
            $recordUid = array_keys($queryParams['edit']['tt_content'])[0];
            $recordUid = (int)abs($recordUid);
            // TcaCTypeItems: edit record
            $record = BackendUtility::getRecord('tt_content', $recordUid, 'tx_container_parent');
            if (isset($record['tx_container_parent'])) {
                return (int)$record['tx_container_parent'];
            }
        }
        return null;
    }
}
