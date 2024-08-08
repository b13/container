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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContainerGridColumn extends GridColumn
{
    public const CONTAINER_COL_POS_DELIMITER = '-';

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
        if (!($this->definition['allowDirectNewLink'] ?? false)) {
            return parent::getNewContentUrl();
        }

        $pageId = $this->context->getPageId();

        $urlParameters = [
            'edit' => [
                'tt_content' => [
                    $pageId => 'new',
                ],
            ],
            'defVals' => [
                'tt_content' => [
                    'colPos' => $this->getColumnNumber(),
                    // @extensionScannerIgnoreLine
                    'sys_language_uid' => $this->container->getLanguage(),
                    'tx_container_parent' => $this->container->getUidOfLiveWorkspace(),
                    'uid_pid' => $this->newContentElementAtTopTarget,
                ],
            ],
            // @extensionScannerIgnoreLine
            'returnUrl' => $this->getRequest()->getAttribute('normalizedParams')->getRequestUri(),
        ];
        $routeName = 'record_edit';

        $allowed = $this->definition['allowed'] ?? [];
        if (!empty($allowed)) {
            $cType = $allowed['CType'] ?? '';
            if ($cType) {
                $urlParameters['defVals']['tt_content']['CType'] = $cType;
            }

            $listType = $allowed['list_type'] ?? '';
            if ($listType) {
                $urlParameters['defVals']['tt_content']['list_type'] = $listType;
            }
        }

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute($routeName, $urlParameters);
    }

    public function getChildAllowedTypesCount(): int
    {
        if (!($this->definition['allowDirectNewLink'] ?? false)) {
            return PHP_INT_MAX;
        }

        $allowed = $this->definition['allowed'] ?? [];
        $cType = explode(',', $allowed['CType'] ?? '');
        $listType = explode(',', $allowed['list_type'] ?? '');

        return count($cType)
            // only add list type count if list is in cType minus 1 for the list cType itself
            + (in_array('list', $cType) ? count($listType) - 1 : 0);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
