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
use B13\Container\Tca\Registry;

class ContainerService
{
    public function __construct(protected Registry $tcaRegistry, protected ContainerFactory $containerFactory)
    {
    }

    public function getNewContentElementAtTopTargetInColumn(Container $container, int $targetColPos): int
    {
        $target = -$container->getUid();
        $previousRecord = null;
        $allColumns = $this->tcaRegistry->getAllAvailableColumnsColPos($container->getCType());
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

    public function getAfterContainerRecord(Container $container): array
    {
        $childRecords = $container->getChildRecords();
        if (empty($childRecords)) {
            return $container->getContainerRecord();
        }

        $lastChild = array_pop($childRecords);
        if (!$this->tcaRegistry->isContainerElement($lastChild['CType'])) {
            return $lastChild;
        }

        $container = $this->containerFactory->buildContainer((int)$lastChild['uid']);
        return $this->getAfterContainerRecord($container);
    }

    public function getAfterContainerElementTarget(Container $container): int
    {
        $target = $this->getAfterContainerRecord($container);

        return -$target['uid'];
    }
}
