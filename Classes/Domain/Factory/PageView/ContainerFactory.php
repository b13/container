<?php

namespace B13\Container\Domain\Factory\PageView;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class ContainerFactory extends \B13\Container\Domain\Factory\ContainerFactory
{
    /**
     * @var ContentStorage
     */
    protected $contentStorage;

    protected function children(array $containerRecord, $language)
    {
        return $this->contentStorage->getContainerChildren($containerRecord, $language);
    }


    protected function containerByUid($uid)
    {
        return $this->database->fetchOneRecord($uid);
    }

    protected function defaultContainer(array $localizedContainer)
    {
        if (isset($localizedContainer['_ORIG_uid'])) {
            $localizedContainer = $this->database->fetchOneRecord($localizedContainer['uid']);
        }
        $defaultRecord = $this->database->fetchOneDefaultRecord($localizedContainer);
        if ($defaultRecord === null) {
            return null;
        }
        return $defaultRecord;
    }
}
