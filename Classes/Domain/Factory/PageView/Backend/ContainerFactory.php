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

use B13\Container\Domain\Factory\Database;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Context\Context;

class ContainerFactory extends \B13\Container\Domain\Factory\ContainerFactory
{
    /**
     * @var ContentStorage
     */
    protected $contentStorage;

    public function __construct(
        Database $database,
        Registry $tcaRegistry,
        Context $context,
        ContentStorage $contentStorage
    ) {
        parent::__construct($database, $tcaRegistry, $context);
        $this->contentStorage = $contentStorage;
    }

    protected function children(array $containerRecord, int $language): array
    {
        return $this->contentStorage->getContainerChildren($containerRecord, $language);
    }

    protected function localizedRecordsByDefaultRecords(array $defaultRecords, int $language): array
    {
        $childRecords = parent::localizedRecordsByDefaultRecords($defaultRecords, $language);
        return $this->contentStorage->workspaceOverlay($childRecords);
    }

    protected function containerByUid(int $uid): ?array
    {
        $record =  $this->database->fetchOneRecord($uid);
        if ($record === null) {
            return null;
        }
        return $this->contentStorage->containerRecordWorkspaceOverlay($record);
    }

    protected function defaultContainer(array $localizedContainer): ?array
    {
        if (isset($localizedContainer['_ORIG_uid'])) {
            $localizedContainer = $this->database->fetchOneRecord((int)$localizedContainer['uid']);
        }
        $defaultRecord = $this->database->fetchOneDefaultRecord($localizedContainer);
        if ($defaultRecord === null) {
            return null;
        }
        return $this->contentStorage->containerRecordWorkspaceOverlay($defaultRecord);
    }
}
