<?php

declare(strict_types=1);

namespace B13\Container\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\Exception;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Domain\Repository\Localization\LocalizationRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Information\Typo3Version;

#[AsEventListener(identifier: 'tx-container-content-used-on-page')]
class ContentUsedOnPage
{
    public function __construct(
        protected ContainerFactory $containerFactory,
        protected Registry $tcaRegistry,
        protected LocalizationRepository $localizationRepository
    ) {
    }

    public function __invoke(IsContentUsedOnPageLayoutEvent $event): void
    {
        $record = $event->getRecord();
        if ($record['tx_container_parent'] > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$record['tx_container_parent']);
                if (($record['sys_language_uid'] ?? 0) > 0 && ($record['l18n_parent'] ?? 0) > 0) {
                    // child is connected, assure container is connected too
                    if ($this->assureContainerIsConnected($record) === false) {
                        $event->setUsed(false);
                        return;
                    }
                }

                $columns = $this->tcaRegistry->getAvailableColumns($container->getCType());
                foreach ($columns as $column) {
                    if ($column['colPos'] === (int)$record['colPos']) {
                        if ($record['sys_language_uid'] > 0 && $container->isConnectedMode()) {
                            $used = $container->hasChildInColPos((int)$record['colPos'], (int)$record['l18n_parent']);
                            $event->setUsed($used);
                            return;
                        }
                        $used = $container->hasChildInColPos((int)$record['colPos'], (int)$record['uid']);
                        $event->setUsed($used);
                        return;
                    }
                }
            } catch (Exception $e) {
            }
        }
    }

    protected function assureContainerIsConnected(array $record): bool
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            $translations = BackendUtility::getRecordLocalization(
                'tt_content',
                (int)$record['tx_container_parent'],
                (int)$record['sys_language_uid']
            );

            if ($translations === false || empty($translations[0]) || (int)($translations[0]['l18n_parent'] ?? 0) !== (int)$record['tx_container_parent']) {
                return false;
            }
        } else {
            $translation = $this->localizationRepository->getRecordTranslation(
                'tt_content',
                (int)$record['tx_container_parent'],
                (int)$record['sys_language_uid']
            );
            if ($translation === null || (int)$translation->getRawRecord()->get('l18n_parent') !== (int)$record['tx_container_parent']) {
                return false;
            }
        }
        return true;
    }
}
