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

class WrongParentError implements ErrorInterface
{
    private const IDENTIFIER = 'WrongParentError';

    protected array $containerRecord;
    protected string $errorMessage;

    public function __construct(array $containerRecord)
    {
        $this->containerRecord = $containerRecord;
        $this->errorMessage = self::IDENTIFIER . ': container uid ' . $containerRecord['uid'] .
            ' (page: ' . $containerRecord['pid'] . ' language: ' . $containerRecord['sys_language_uid'] . ')' .
            ' has tx_container_parent ' . $containerRecord['tx_container_parent'];
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getSeverity(): int
    {
        return ErrorInterface::ERROR;
    }

    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }
}
