<?php

declare(strict_types=1);

namespace B13\Container\Domain\Service;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\SingletonInterface;

class ContainerService implements SingletonInterface
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    public function __construct(ConfigurationService $configurationService, ContainerFactory $containerFactory)
    {
        $this->configurationService = $configurationService;
        $this->containerFactory = $containerFactory;
    }

    public function getNewContentElementAtTopTargetInColumn(Container $container, int $targetColPos): int
    {
        $target = -$container->getUid();
        $previousRecord = null;
        $allColumns = $this->configurationService->getAllAvailableColumnsColPos($container->getCType());
        foreach ($allColumns as $colPos) {
            if ($colPos === $targetColPos && $previousRecord !== null) {
                $target = -(int)$previousRecord['uid'];
            }
            $children = $container->getChildrenByColPos($colPos);
            if (!empty($children)) {
                $last = array_pop($children);
                $previousRecord = $last;
            }
        }
        return $target;
    }

    public function getAfterContainerElementTarget(Container $container): int
    {
        $target = -$container->getUid();
        $childRecords = $container->getChildRecords();
        if (empty($childRecords)) {
            return $target;
        }
        $lastChild = array_pop($childRecords);
        if (!$this->configurationService->isContainerElement($lastChild['CType'])) {
            return -(int)$lastChild['uid'];
        }
        $container = $this->containerFactory->buildContainer((int)$lastChild['uid']);
        return $this->getAfterContainerElementTarget($container);
    }
}
