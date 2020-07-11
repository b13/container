<?php

namespace B13\Container\Integrity\Error;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Messaging\AbstractMessage;


class WrongL18nParentError implements ErrorInterface
{
    /**
     * @var array
     */
    protected $childRecord = null;

    /**
     * @var array
     */
    protected $containerRecord = null;

    /**
     * @var string
     */
    protected $errorMessage = null;

    /**
     * @param array $childRecord
     * @param array $containerRecord
     */
    public function __construct(array $childRecord, array $containerRecord)
    {
        $this->childRecord = $childRecord;
        $this->containerRecord = $containerRecord;
        $this->errorMessage = 'container child with uid ' . $childRecord['uid'] .
            ' has l18n_parent ' . $childRecord['l18n_parent']
            . ' but tx_container_parent ' . $childRecord['tx_container_parent']
            . ' has l18n_parent ' . $containerRecord['l18n_parent'];

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
    public function getChildRecord(): array
    {
        return $this->childRecord;
    }

    /**
     * @return array
     */
    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }

}
