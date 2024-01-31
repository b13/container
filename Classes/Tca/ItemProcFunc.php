<?php

declare(strict_types=1);

namespace B13\Container\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Backend\View\BackendLayoutView;

class ItemProcFunc
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @var BackendLayoutView
     */
    protected $backendLayoutView;

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(ContainerFactory $containerFactory, Registry $tcaRegistry, BackendLayoutView $backendLayoutView)
    {
        $this->containerFactory = $containerFactory;
        $this->tcaRegistry = $tcaRegistry;
        $this->backendLayoutView = $backendLayoutView;
    }

    /**
     * Gets colPos items to be shown in the forms engine.
     * This method is called as "itemsProcFunc" with the accordant context
     * for tt_content.colPos.
     */
    public function colPos(array &$parameters): void
    {
        $row = $parameters['row'];
        if (($row['tx_container_parent'] ?? 0) > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$row['tx_container_parent']);
                $cType = $container->getCType();
                $grid = $this->tcaRegistry->getGrid($cType);
                if (is_array($grid)) {
                    $items = [];
                    foreach ($grid as $rows) {
                        foreach ($rows as $column) {
                            // only one item is show, so it is not changeable
                            if ((int)$column['colPos'] === (int)$row['colPos']) {
                                $items[] = [
                                    $column['name'],
                                    $column['colPos'],
                                ];
                            }
                        }
                    }
                    $parameters['items'] = $items;
                    return;
                }
            } catch (Exception $e) {
            }
        }

        $this->backendLayoutView->colPosListItemProcFunc($parameters);
    }

    public function txContainerParent(array &$parameters): void
    {
        $row = $parameters['row'];
        $items = [];
        if (($row['tx_container_parent'] ?? 0) > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$row['tx_container_parent']);
                $cType = $container->getCType();
                $items[] = [
                    $cType,
                    $row['tx_container_parent'],
                ];
            } catch (Exception $e) {
                $items[] = [
                    '-',
                    0,
                ];
            }
        } else {
            $items[] = [
                '-',
                0,
            ];
        }
        $parameters['items'] = $items;
    }
}
