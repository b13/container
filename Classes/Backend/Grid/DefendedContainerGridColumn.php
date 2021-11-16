<?php

declare(strict_types=1);

namespace B13\Container\Backend\Grid;

use IchHabRecht\ContentDefender\BackendLayout\BackendLayoutConfiguration;

class DefendedContainerGridColumn extends ContainerGridColumn
{
    public function getAllowNewContent(): bool
    {
        $children = $this->container->getChildrenByColPos($this->columnNumber);
        if (count($children) > 0) {
            return $this->isNewRecordAllowedByItemsCount($children[0]);
        }

        return parent::getAllowNewContent();
    }

    /**
     * @param array $childRecord
     *
     * @return bool
     */
    protected function isNewRecordAllowedByItemsCount(array $childRecord): bool
    {
        $backendLayoutConfiguration = BackendLayoutConfiguration::createFromPageId($childRecord['pid']);
        $columnConfiguration = $backendLayoutConfiguration->getConfigurationByColPos($childRecord['colPos'], $childRecord['uid']);

        if (!isset($columnConfiguration['maxitems'])) {
            return true;
        }

        return $columnConfiguration['maxitems'] > count($this->container->getChildrenByColPos($this->columnNumber));
    }
}
