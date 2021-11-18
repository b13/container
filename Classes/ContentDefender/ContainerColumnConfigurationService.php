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

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Model\Container;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerColumnConfigurationService implements SingletonInterface
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

    public function isMaxitemsReachedByContainenrId(int $containerId, int $colPos): bool
    {
        try {
            $container = $this->containerFactory->buildContainer($containerId);
            return $this->isMaxitemsReached($container, $colPos);
        } catch (Exception $e) {
            // not a container
        }
        return false;
    }

    public function isMaxitemsReached(Container $container, int $colPos): bool
    {
        $columnConfiguration = $this->getColumnConfigurationForContainer($container, $colPos);
        if (!isset($columnConfiguration['maxitems']) || (int)$columnConfiguration['maxitems'] === 0) {
            return false;
        }
        $childrenOfColumn = $container->getChildrenByColPos($colPos);
        return count($childrenOfColumn) >= $columnConfiguration['maxitems'];
    }
}
