<?php

namespace B13\Container\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\Registry;

class IntegrityFactory implements SingletonInterface
{

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;


    /**
     * ContainerFactory constructor.
     * @param Database|null $database
     * @param Registry|null $tcaRegistry
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    public function childsHasSamePidAsContainer(): array
    {
        return [];
    }

    public function childsHasColPosAvailableInContainerContainer(): array
    {
        return [];
    }

}
