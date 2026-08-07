<?php

declare(strict_types=1);

namespace B13\Container\Domain\Core;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\Domain\Record\LanguageInfo;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;
use TYPO3\CMS\Core\Domain\Record\VersionInfo;
use TYPO3\CMS\Core\Domain\RecordInterface;

class RecordWithRenderedGrid implements RecordInterface
{
    public function __construct(
        protected readonly Record $coreRecord,
        protected readonly ?string $renderedGrid
    ) {
    }

    public function getUid(): int
    {
        return $this->coreRecord->getRawRecord()->getUid();
    }

    public function getPid(): int
    {
        return $this->coreRecord->getRawRecord()->getPid();
    }

    public function getFullType(): string
    {
        return $this->coreRecord->getRawRecord()->getFullType();
    }

    public function getRecordType(): ?string
    {
        return $this->coreRecord->getRawRecord()->getRecordType();
    }

    public function getMainType(): string
    {
        return $this->coreRecord->getRawRecord()->getMainType();
    }

    public function toArray(bool $includeSystemProperties = false): array
    {
        $properties = $this->coreRecord->toArray();
        $properties['tx_container_grid'] = $this->renderedGrid;
        return $properties;
    }

    public function has(string $id): bool
    {
        if ($id === 'tx_container_grid') {
            return true;
        }
        return $this->coreRecord->has($id);
    }

    public function get(string $id): mixed
    {
        if ($id === 'tx_container_grid') {
            return $this->renderedGrid;
        }
        return $this->coreRecord->get($id);
    }

    public function getVersionInfo(): ?VersionInfo
    {
        return $this->coreRecord->getVersionInfo();
    }

    public function getLanguageInfo(): ?LanguageInfo
    {
        return $this->coreRecord->getLanguageInfo();
    }

    public function getLanguageId(): ?int
    {
        return $this->coreRecord->getLanguageId();
    }

    public function getSystemProperties(): ?SystemProperties
    {
        return $this->coreRecord->getSystemProperties();
    }

    public function getComputedProperties(): ComputedProperties
    {
        return $this->coreRecord->getComputedProperties();
    }

    public function getRawRecord(): RawRecord
    {
        return $this->coreRecord->getRawRecord();
    }

    public function getOverlaidUid(): int
    {
        return $this->coreRecord->getOverlaidUid();
    }
}
