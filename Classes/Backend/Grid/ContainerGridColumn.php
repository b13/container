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
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerGridColumn extends GridColumn
{
    public const CONTAINER_COL_POS_DELIMITER = '-';

    public const CONTAINER_COL_POS_DELIMITER_V12 = 999990;

    protected $container;

    protected $allowNewContentElements = true;

    protected $newContentElementAtTopTarget;

    public function __construct(
        PageLayoutContext $context,
        array $columnDefinition,
        Container $container,
        int $newContentElementAtTopTarget,
        bool $allowNewContentElements = true
    ) {
        parent::__construct($context, $columnDefinition);
        $this->container = $container;
        $this->allowNewContentElements = $allowNewContentElements;
        $this->newContentElementAtTopTarget = $newContentElementAtTopTarget;
    }

    public function getDataColPos(): string
    {
        // we return a string because of 32-bit system PHP_INT_MAX
        return (string)$this->getContainerUid() . (string)self::CONTAINER_COL_POS_DELIMITER_V12 . (string)$this->getColumnNumber();
    }

    public function getContainerUid(): int
    {
        return $this->container->getUidOfLiveWorkspace();
    }

    public function getTitle(): string
    {
        return (string)$this->getLanguageService()->sL($this->getColumnName());
    }

    public function getAllowNewContent(): bool
    {
        if ($this->container->getLanguage() > 0 && $this->container->isConnectedMode()) {
            return false;
        }
        return $this->allowNewContentElements;
    }

    public function isActive(): bool
    {
        // yes we are active
        return true;
    }

    public function getNewContentUrl(): string
    {
        $pageId = $this->context->getPageId();
        $urlParameters = [
            'id' => $pageId,
            'sys_language_uid' => $this->container->getLanguage(),
            'colPos' => $this->getColumnNumber(),
            'tx_container_parent' => $this->container->getUidOfLiveWorkspace(),
            'uid_pid' => $this->newContentElementAtTopTarget,
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
    }
}
