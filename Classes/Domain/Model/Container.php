<?php

declare(strict_types=1);

namespace B13\Container\Domain\Model;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class Container
{
    public function __construct(protected array $containerRecord, protected array $childRecords, protected $language = 0)
    {
    }

    public function getUid(): int
    {
        return (int)$this->containerRecord['uid'];
    }

    public function getUidOfLiveWorkspace(): int
    {
        if (isset($this->containerRecord['t3ver_oid']) && $this->containerRecord['t3ver_oid'] > 0) {
            return (int)$this->containerRecord['t3ver_oid'];
        }
        return $this->getUid();
    }

    public function getPid(): int
    {
        if (!empty($this->containerRecord['_ORIG_pid'])) {
            return (int)$this->containerRecord['_ORIG_pid'];
        }
        return (int)$this->containerRecord['pid'];
    }

    public function isConnectedMode(): bool
    {
        return (int)$this->containerRecord['sys_language_uid'] === 0;
    }

    public function getLanguage(): int
    {
        return $this->language;
    }

    public function getCType(): string
    {
        return $this->containerRecord['CType'];
    }

    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }

    public function getChildRecords(): array
    {
        $childRecords = [];
        foreach ($this->childRecords as $colPos => $records) {
            $childRecords = array_merge($childRecords, $records);
        }
        return $childRecords;
    }

    public function getChildrenByColPos(int $colPos): array
    {
        if (empty($this->childRecords[$colPos])) {
            return [];
        }
        return $this->childRecords[$colPos];
    }

    public function hasChildInColPos(int $colPos, int $childUid): bool
    {
        if (!isset($this->childRecords[$colPos])) {
            return false;
        }
        foreach ($this->childRecords[$colPos] as $childRecord) {
            if ((int)$childRecord['uid'] === $childUid) {
                return true;
            }
        }
        return false;
    }

    public function getChildrenColPos(): array
    {
        return array_keys($this->childRecords);
    }
}
