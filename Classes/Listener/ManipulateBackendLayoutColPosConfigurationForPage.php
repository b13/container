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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ManipulateBackendLayoutColPosConfigurationForPage
{
    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    public function __construct(ContainerFactory $containerFactory, Registry $tcaRegistry)
    {
        $this->containerFactory = $containerFactory;
        $this->tcaRegistry = $tcaRegistry;
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
          //  'name' => 'sfaddsf',
          //  'colPos' => $e->colPos,
            'allowedContentTypes' => $configuration['allowedContentTypes'],
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
            https://core14.ddev.site/typo3/record/edit?edit%5Btt_content%5D%5B11%5D=edit
            $recordUid = array_keys($queryParams['edit']['tt_content'])[0];
            // DebuggerUtility::var_dump($recordUid);
            // TcaCTypeItems: edit record
            $record = BackendUtility::getRecord('tt_content', $recordUid, 'tx_container_parent');
            if (isset($record['tx_container_parent'])) {
                return (int)$record['tx_container_parent'];
            }
        }
        // https://core14.ddev.site/typo3/record/edit?edit%5Btt_content%5D%5B-11%5D=new&
        return null;
    }
}
