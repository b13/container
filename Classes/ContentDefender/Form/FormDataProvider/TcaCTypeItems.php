<?php

declare(strict_types = 1);

namespace B13\Container\ContentDefender\Form\FormDataProvider;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaCTypeItems implements FormDataProviderInterface
{

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

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
     * @param array $result
     * @return array
     */
    public function addData(array $result)
    {
        if ('tt_content' !== $result['tableName']) {
            return $result;
        }
        $colPos = (int)$result['databaseRow']['colPos'][0];
        $parent = (int)$result['databaseRow']['tx_container_parent'][0];
        if ($parent > 0 && $colPos > 0) {
            try {
                $container = $this->containerFactory->buildContainer($parent);
                $cType = $container->getCType();
                $allowedConfiguration = $this->tcaRegistry->getAllowedConfiguration($cType, $colPos);
                foreach ($allowedConfiguration as $field => $value) {
                    $allowedValues = GeneralUtility::trimExplode(',', $value);
                    $result['processedTca']['columns'][$field]['config']['items'] = array_filter(
                        $result['processedTca']['columns'][$field]['config']['items'],
                        static function ($item) use ($allowedValues) {
                            return in_array($item[1], $allowedValues);
                        }
                    );
                }
            } catch (Exception $e) {
                // not a container
            }
        }
        return $result;
    }
}
