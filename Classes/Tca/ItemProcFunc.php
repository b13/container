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
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\View\BackendLayoutView;

#[Autoconfigure(public: true)]
class ItemProcFunc
{
    public function __construct(
        protected ContainerFactory $containerFactory,
        protected Registry $tcaRegistry,
        protected BackendLayoutView $backendLayoutView
    ) {
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
                                    'label' => $column['name'],
                                    'value' => $column['colPos'],
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
                    'label' => $this->tcaRegistry->getContainerLabel($cType),
                    'value' => $row['tx_container_parent'],
                ];
            } catch (Exception $e) {
                $items[] = [
                    'label' => '-',
                    'value' =>  0,
                ];
            }
        } else {
            $items[] = [
                'label' => '-',
                'value' => 0,
            ];
        }
        $parameters['items'] = $items;
    }
}
