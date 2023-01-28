<?php

declare(strict_types=1);

namespace B13\Container\Listener;

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
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;

class ContentUsedOnPage
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

    public function __invoke(IsContentUsedOnPageLayoutEvent $event): void
    {
        $record = $event->getRecord();
        if ($record['tx_container_parent'] > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$record['tx_container_parent']);
                $columns = $this->tcaRegistry->getAvailableColumns($container->getCType());
                foreach ($columns as $column) {
                    if ($column['colPos'] === (int)$record['colPos']) {
                        if ($record['sys_language_uid'] > 0 && $container->isConnectedMode()) {
                            $used = $container->hasChildInColPos((int)$record['colPos'], (int)$record['l18n_parent']);
                            $event->setUsed($used);
                            return;
                        }
                        $used = $container->hasChildInColPos((int)$record['colPos'], (int)$record['uid']);
                        $event->setUsed($used);
                        return;
                    }
                }
                $event->setUsed(false);
                return;
            } catch (Exception $e) {
            }
        }
    }
}
