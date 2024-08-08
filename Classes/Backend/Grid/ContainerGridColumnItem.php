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
        if (!($this->column->getDefinition()['allowDirectNewLink'] ?? false)) {
            return parent::getNewContentAfterUrl();
        }

        $urlParameters = [
            'edit' => [
                'tt_content' => [
                    -$this->record['uid'] => 'new',
                ],
            ],
            'defVals' => [
                'tt_content' => [
                    'colPos' => $this->column->getColumnNumber(),
                    // @extensionScannerIgnoreLine
                    'sys_language_uid' => $this->container->getLanguage(),
                    'tx_container_parent' => $this->container->getUidOfLiveWorkspace(),
                    'uid_pid' => -$this->record['uid'],
                ],
            ],
            // @extensionScannerIgnoreLine
            'returnUrl' => $this->getRequest()->getAttribute('normalizedParams')->getRequestUri(),
        ];
        $routeName = 'record_edit';

        $allowed = $this->column->getDefinition()['allowed'] ?? [];
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

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
