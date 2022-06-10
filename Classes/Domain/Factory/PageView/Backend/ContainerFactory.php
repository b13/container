<?php

declare(strict_types=1);

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
use TYPO3\CMS\Core\Context\Context;

class ContainerFactory extends \B13\Container\Domain\Factory\PageView\ContainerFactory
{
    /**
     * @var ContentStorage
     */
    protected $contentStorage;

    public function __construct(
        Database $database,
        Registry $tcaRegistry,
        Context $context,
        ContentStorage $contentStorage
    ) {
        parent::__construct($database, $tcaRegistry, $context);
        $this->contentStorage = $contentStorage;
    }
}
