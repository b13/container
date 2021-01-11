<?php

namespace B13\Container\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerFactory extends \B13\Container\Domain\Factory\PageView\ContainerFactory
{
    /**
     * @var ContentStorage
     */
    protected $contentStorage;

    public function __construct(
        Database $database = null,
        Registry $tcaRegistry = null,
        ContentStorage $contentStorage = null
    ) {
        parent::__construct($database, $tcaRegistry);
        if ($contentStorage === null) {
            $contentStorage = GeneralUtility::makeInstance(ContentStorage::class);
        }
        $this->contentStorage = $contentStorage;
    }
}
