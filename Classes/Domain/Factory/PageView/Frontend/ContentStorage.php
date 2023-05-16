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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

class ContentStorage extends \B13\Container\Domain\Factory\PageView\ContentStorage
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    public function __construct(Database $database, Context $context, PageRepository $pageRepository)
    {
        parent::__construct($database, $context);
        $this->pageRepository = $pageRepository;
    }

    public function getPageRepository(): PageRepository
    {
        return $this->pageRepository;
    }

    public function containerRecordWorkspaceOverlay(array $record): ?array
    {
        $this->pageRepository->versionOL('tt_content', $record, false);
        if (is_array($record)) {
            return $record;
        }
        return null;
    }

    public function workspaceOverlay(array $records): array
    {
        $filtered = [];
        foreach ($records as $row) {
            $this->pageRepository->versionOL('tt_content', $row, true);
            // Language overlay:
            if (is_array($row)) {
                //$row = $this->pageRepository->getLanguageOverlay('table', $row);
            }
            // Might be unset in the language overlay
            if (is_array($row)) {
                $filtered[] = $row;
            }
        }
        return $filtered;
    }
}
