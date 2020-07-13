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
    /**
     * @var array
     */
    protected $containerRecord;

    /**
     * @var array
     */
    protected $childRecords;

    /**
     * @var int
     */
    protected $language = 0;

    /**
     * @param array $containerRecord
     * @param array $childRecords
     * @param int $language
     */
    public function __construct(array $containerRecord, array $childRecords, $language = 0)
    {
        $this->containerRecord = $containerRecord;
        $this->childRecords = $childRecords;
        $this->language = $language;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return (int)$this->containerRecord['uid'];
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return (int)$this->containerRecord['pid'];
    }

    /**
     * @return bool
     */
    public function isConnectedMode(): bool
    {
        return (int)$this->containerRecord['sys_language_uid'] === 0;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getCType(): string
    {
        return $this->containerRecord['CType'];
    }

    /**
     * @return array
     */
    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }

    /**
     * @return array
     */
    public function getChildRecords(): array
    {
        $childRecords = [];
        foreach ($this->childRecords as $colPos => $records) {
            $childRecords = array_merge($childRecords, $records);
        }
        return $childRecords;
    }

    /**
     * @param int $colPos
     * @return array
     */
    public function getChildrenByColPos(int $colPos): array
    {
        if (empty($this->childRecords[$colPos])) {
            return [];
        }
        return $this->childRecords[$colPos];
    }

    /**
     * @return array
     */
    public function getChildrenColPos(): array
    {
        return array_keys($this->childRecords);
    }
}
