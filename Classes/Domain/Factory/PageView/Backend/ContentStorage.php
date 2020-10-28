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

use TYPO3\CMS\Backend\Utility\BackendUtility;

class ContentStorage extends \B13\Container\Domain\Factory\PageView\ContentStorage
{
    public function workspaceOverlay(array $records): array
    {
        $filtered = [];
        foreach ($records as $row) {
            BackendUtility::workspaceOL('tt_content', $row, $this->workspaceId, true);
            if (is_array($row)) {
                $filtered[] = $row;
            }
        }
        return $filtered;
    }

    public function containerRecordWorkspaceOverlay(array $record): ?array
    {
        BackendUtility::workspaceOL('tt_content', $record, $this->workspaceId, false);
        if (is_array($record)) {
            return $record;
        }
        return null;
    }
}
