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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public function __construct(
        ContainerFactory $containerFactory = null,
        Registry $tcaRegistry = null
    ) {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    public function manipulateConfiguration(array $configuration, int $colPos, $recordUid): array
    {
        $parent = $this->getParentUid($recordUid);
        if ($parent === 0) {
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
        return $configuration;
    }

    private function getParentUid($recordUid): int
    {
        $parent = 0;
        if (empty($parent)) {
            // new content elemment wizard
            $parent = GeneralUtility::_GP('tx_container_parent');
        }
        if (empty($parent)) {
            // TcaCTypeItems: new record
            $defVals = GeneralUtility::_GP('defVals');
            $parent = $defVals['tt_content']['tx_container_parent'] ?? 0;
        }
        if (empty($parent)) {
            $edit = GeneralUtility::_GP('edit');
            if (isset($edit['tt_content'][$recordUid])) {
                // TcaCTypeItems: edit record
                $record = BackendUtility::getRecord('tt_content', $recordUid, 'tx_container_parent');
                $parent = $record['tx_container_parent'] ?? 0;
            }
        }
        return (int)$parent;
    }
}
