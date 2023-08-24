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

use TYPO3\CMS\Core\Messaging\AbstractMessage;

class WrongParentError implements ErrorInterface
{
    private const IDENTIFIER = 'WrongParentError';

    /**
     * @var array
     */
    protected $containerRecord;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @param array $containerRecord
     */
    public function __construct(array $containerRecord)
    {
        $this->containerRecord = $containerRecord;
        $this->errorMessage = self::IDENTIFIER . ': container uid ' . $containerRecord['uid'] .
            ' (page: ' . $containerRecord['pid'] . ' language: ' . $containerRecord['sys_language_uid'] . ')' .
            ' has tx_container_parent ' . $containerRecord['tx_container_parent'];
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return AbstractMessage::ERROR;
    }

    /**
     * @return array
     */
    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }
}
