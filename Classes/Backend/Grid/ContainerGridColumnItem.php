<?php

declare(strict_types=1);

namespace B13\Container\Backend\Grid;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerGridColumnItem extends GridColumnItem
{
    protected $container;

    public function __construct(PageLayoutContext $context, GridColumn $column, array $record, Container $container)
    {
        parent::__construct($context, $column, $record);
        $this->container = $container;
    }

    public function getAllowNewContent(): bool
    {
        if ($this->container->getLanguage() > 0 && $this->container->isConnectedMode()) {
            return false;
        }
        return true;
    }

    public function getWrapperClassName(): string
    {
        $wrapperClassNames = [];
        if ($this->isDisabled()) {
            $wrapperClassNames[] = 't3-page-ce-hidden t3js-hidden-record';
        }
        // we do not need a "t3-page-ce-warning" class because we are build from Container
        return implode(' ', $wrapperClassNames);
    }

    public function getNewContentAfterUrl(): string
    {
        $pageId = $this->context->getPageId();
        $urlParameters = [
            'id' => $pageId,
            'sys_language_uid' => $this->container->getLanguage(),
            'colPos' => $this->column->getColumnNumber(),
            'tx_container_parent' => $this->container->getUidOfLiveWorkspace(),
            'uid_pid' => -$this->record['uid'],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
    }
}
