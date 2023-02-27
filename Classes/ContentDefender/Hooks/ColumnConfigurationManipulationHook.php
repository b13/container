<?php

declare(strict_types=1);

namespace B13\Container\ContentDefender\Hooks;

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
use IchHabRecht\ContentDefender\BackendLayout\ColumnConfigurationManipulationInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\ServerRequest;

class ColumnConfigurationManipulationHook implements ColumnConfigurationManipulationInterface
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

    public function manipulateConfiguration(array $configuration, int $colPos, $recordUid): array
    {
        $parent = $this->getParentUid($recordUid);
        if ($parent === null) {
            return $configuration;
        }
        try {
            $container = $this->containerFactory->buildContainer($parent);
        } catch (Exception $e) {
            // not a container
            return $configuration;
        }
        $cType = $container->getCType();
        $configuration = $this->tcaRegistry->getContentDefenderConfiguration($cType, $colPos);
        // maxitems needs not to be considered in this case
        // (new content elemment wizard, TcaCTypeItems: new record, TcaCTypeItems: edit record)
        // consider maxitems here leeds to errors, because relation to container gets lost in EXT:content_defender
        // EXT:container has already a own solution to prevent new records inside a container if maxitems is reached
        // "New Content" Button is not rendered inside die colPos, this is possible because EXT:container has its own templates
        $configuration['maxitems'] = 0;
        return $configuration;
    }

    private function getParentUid($recordUid): ?int
    {
        $request = $this->getServerRequest();
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
        if (isset($queryParams['edit']['tt_content'][$recordUid])) {
            // TcaCTypeItems: edit record
            $record = BackendUtility::getRecord('tt_content', $recordUid, 'tx_container_parent');
            if (isset($record['tx_container_parent'])) {
                return (int)$record['tx_container_parent'];
            }
        }
        return null;
    }

    protected function getServerRequest(): ?ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
