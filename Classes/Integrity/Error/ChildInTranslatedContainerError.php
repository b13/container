<?php

declare(strict_types=1);

namespace B13\Container\Integrity\Error;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class ChildInTranslatedContainerError implements ErrorInterface
{
    private const IDENTIFIER = 'ChildInTranslatedContainerError';

    protected array $childRecord;
    protected array $containerRecord;
    protected string $errorMessage;

    public function __construct(array $childRecord, array $containerRecord)
    {
        $this->childRecord = $childRecord;
        $this->containerRecord = $containerRecord;
        $this->errorMessage = self::IDENTIFIER . ': translated container child with uid ' . $childRecord['uid'] .
            ' (page: ' . $childRecord['pid'] . ' language: ' . $childRecord['sys_language_uid'] . ')' .
            ' has translated tx_container_parent ' . $containerRecord['uid']
            . ' but should point to default language container record ' . $containerRecord['l18n_parent'];
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getSeverity(): int
    {
        return ErrorInterface::ERROR;
    }

    public function getChildRecord(): array
    {
        return $this->childRecord;
    }

    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }
}
