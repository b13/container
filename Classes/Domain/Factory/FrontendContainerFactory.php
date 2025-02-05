<?php

declare(strict_types=1);

namespace B13\Container\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Model\Container;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FrontendContainerFactory implements SingletonInterface
{

    protected Registry $tcaRegistry;

    public function __construct(Registry $tcaRegistry)
    {
        $this->tcaRegistry = $tcaRegistry;
    }

    public function buildContainer(ContentObjectRenderer $cObj, Context $context, ?int $uid = null): Container
    {
        if ($uid === null) {
            $record = $cObj->data;
        } else {
            $records = $cObj->getRecords('tt_content', ['where' => 'uid=' . $uid]);
            if (empty($records)) {
                throw new Exception('no record ' . $uid, 1734946029);
            }
            $record = $records[0];
        }
        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
            throw new Exception('not a container element with uid ' . $uid, 1734946028);
        }
        $conf = ['where' => 'tx_container_parent=' . $record['uid'], 'orderBy' => 'sorting'];
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');
        if ($languageAspect->getOverlayType() === LanguageAspect::OVERLAYS_OFF && $record['l18n_parent'] > 0) {
            $conf['where'] .= ' OR tx_container_parent=' . $record['l18n_parent'];
        }
        $childRecords = $cObj->getRecords('tt_content', $conf);
        $childRecords = $this->recordsByColPosKey($childRecords);
        $container = new Container($record, $childRecords, (int)$record['sys_language_uid']);
        return $container;
    }

    protected function recordsByColPosKey(array $records): array
    {
        $recordsByColPosKey = [];
        foreach ($records as $record) {
            if (empty($recordsByColPosKey[$record['colPos']])) {
                $recordsByColPosKey[$record['colPos']] = [];
            }
            $recordsByColPosKey[$record['colPos']][] = $record;
        }
        return $recordsByColPosKey;
    }
}
