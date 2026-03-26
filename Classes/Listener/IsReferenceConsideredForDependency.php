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

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Workspaces\Dependency\DependencyCollectionAction;
use TYPO3\CMS\Workspaces\Event\IsReferenceConsideredForDependencyEvent;

#[AsEventListener(identifier: 'tx-container-is-reference-considered-for-dependency')]
class IsReferenceConsideredForDependency
{
    public function __invoke(IsReferenceConsideredForDependencyEvent $event)
    {
        if (
            $event->getTableName() === 'tt_content' &&
            $event->getFieldName() === 'tx_container_parent' &&
            $event->getReferenceTable() === 'tt_content' &&
            $event->getAction() === DependencyCollectionAction::Publish
        ) {
            $event->setDependency(true);
        }
    }
}
