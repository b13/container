<?php

declare(strict_types=1);

namespace B13\Container\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Grid\ContainerGridColumn;
use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Model\Container;
use B13\Container\Tca\Registry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Utility\BackendUtility;

#[Autoconfigure(public: true)]
class ContainerColumnConfigurationService
{
    protected $copyMapping = [];

    protected $contentDefenderContainerDataHandlerHookIsLocked = false;

    public function __construct(protected ContainerFactory $containerFactory, protected Registry $tcaRegistry)
    {
    }

    public function startCmdMap(): void
    {
        $this->contentDefenderContainerDataHandlerHookIsLocked = true;
    }

    public function endCmdMap(): void
    {
        $this->contentDefenderContainerDataHandlerHookIsLocked = false;
    }

    public function isContentDefenderContainerDataHandlerHookLooked(): bool
    {
        return $this->contentDefenderContainerDataHandlerHookIsLocked;
    }

    protected function getRecord(int $uid): ?array
    {
        return BackendUtility::getRecord('tt_content', $uid);
    }

    public function addCopyMapping(int $sourceContentId, int $containerId, int $targetColpos): void
    {
        $record = $this->getRecord($sourceContentId);
        $sourceColPos = (int)$record['colPos'];
        $sourceContainerId = (int)$record['tx_container_parent'];
        $this->copyMapping[$sourceContainerId . ContainerGridColumn::CONTAINER_COL_POS_DELIMITER . $sourceColPos] = [
            'containerId' => $containerId,
            'sourceColPos' => $sourceColPos,
            'targetColPos' => $targetColpos,
        ];
    }

    public function override(array $columnConfiguration, int $containerId, int $colPos): array
    {
        try {
            $container = $this->containerFactory->buildContainer($containerId);
            $columnConfiguration = $this->getColumnConfigurationForContainer($container, $colPos);
        } catch (Exception $e) {
            // not a container
        }
        return $columnConfiguration;
    }

    protected function getColumnConfigurationForContainer(Container $container, int $colPos): array
    {
        $cType = $container->getCType();
        $columnConfiguration = $this->tcaRegistry->getContentDefenderConfiguration($cType, $colPos);
        return $columnConfiguration;
    }

    public function isMaxitemsReachedByContainenrId(int $containerId, int $colPos, ?int $childUid = null): bool
    {
        try {
            $container = $this->containerFactory->buildContainer($containerId);
            return $this->isMaxitemsReached($container, $colPos, $childUid);
        } catch (Exception $e) {
            // not a container
        }
        return false;
    }

    public function isMaxitemsReached(Container $container, int $colPos, ?int $childUid = null): bool
    {
        $columnConfiguration = $this->getColumnConfigurationForContainer($container, $colPos);
        if (!isset($columnConfiguration['maxitems']) || (int)$columnConfiguration['maxitems'] === 0) {
            return false;
        }
        $childrenOfColumn = $container->getChildrenByColPos($colPos);
        $count = count($childrenOfColumn);
        if ($childUid !== null && $container->hasChildInColPos($colPos, $childUid)) {
            $count--;
        }
        return $count >= $columnConfiguration['maxitems'];
    }
}
