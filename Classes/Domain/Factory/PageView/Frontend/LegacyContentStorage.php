<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory\PageView\Frontend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Database;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

class LegacyContentStorage extends \B13\Container\Domain\Factory\PageView\Frontend\ContentStorage
{

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    public function __construct(Database $database = null, PageRepository $pageRepository = null)
    {
        \B13\Container\Domain\Factory\PageView\ContentStorage::__construct($database);
        $this->pageRepository = $pageRepository ?? GeneralUtility::makeInstance(PageRepository::class);
    }
}
