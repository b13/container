<?php

namespace B13\Container\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\Registry;

class UsedRecords
{

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory = null;

    /**
     * UsedRecords constructor.
     * @param ContainerFactory|null $containerFactory
     * @param Registry|null $tcaRegistry
     */
    public function __construct(ContainerFactory $containerFactory = null, Registry $tcaRegistry = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @param array $params
     * @param PageLayoutView $pageLayoutView
     * @return bool
     */
    public function addContainerChildren(array $params, PageLayoutView $pageLayoutView): bool
    {
        $record = $params['record'];
        if ($record['tx_container_parent'] > 0) {
            try {
                $container = $this->containerFactory->buildContainer($record['tx_container_parent']);
                $columns = $this->tcaRegistry->getAvaiableColumns($container->getCType());
                foreach ($columns as $column) {
                    if ($column['colPos'] === $record['colPos']) {
                        return true;
                    }
                }
                return false;
            } catch (Exception $e) {

            }
        }
        return $params['used'];
    }
}
