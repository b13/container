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

class UnusedColPosWarning implements ErrorInterface
{
    private const IDENTIFIER = 'UnusedColPosWarning';

    protected array $childRecord;
    protected array $containerRecord;
    protected string $errorMessage;

    public function __construct(array $childRecord, array $containerRecord)
    {
        $this->childRecord = $childRecord;
        $this->containerRecord = $containerRecord;
        $this->errorMessage = self::IDENTIFIER . ': container child with uid ' . $childRecord['uid'] .
            ' (page: ' . $childRecord['pid'] . ' language: ' . $childRecord['sys_language_uid'] . ')' .
            ' has invalid colPos ' . $childRecord['colPos']
            . ' in container ' . $childRecord['tx_container_parent']
            . ' with CType ' . $containerRecord['CType'];
    }

    public function getChildRecord(): array
    {
        return $this->childRecord;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getSeverity(): int
    {
        return ErrorInterface::WARNING;
    }
}
