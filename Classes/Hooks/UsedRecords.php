<?php

declare(strict_types=1);

namespace B13\Container\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\PageLayoutView;

class UsedRecords
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

    public function addContainerChildren(array $params, PageLayoutView $pageLayoutView): bool
    {
        $record = $params['record'];

        if (isset($record['tx_container_parent']) && $record['tx_container_parent'] > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$record['tx_container_parent']);
                $columns = $this->tcaRegistry->getAvailableColumns($container->getCType());
                foreach ($columns as $column) {
                    if ($column['colPos'] === (int)$record['colPos']) {
                        if ($record['sys_language_uid'] > 0 && $container->isConnectedMode()) {
                            return $container->hasChildInColPos((int)$record['colPos'], (int)$record['l18n_parent']);
                        }
                        return $container->hasChildInColPos((int)$record['colPos'], (int)$record['uid']);
                    }
                }
                return false;
            } catch (Exception $e) {
            }
        }
        return $params['used'];
    }
}
