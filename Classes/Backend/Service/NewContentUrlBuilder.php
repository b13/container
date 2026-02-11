<?php

declare(strict_types=1);

namespace B13\Container\Backend\Service;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\ContentDefender\ContainerColumnConfigurationService;
use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Tca\Registry;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\PageLayoutContext;

class NewContentUrlBuilder
{
    public function __construct(
        protected Registry $tcaRegistry,
        protected ContainerColumnConfigurationService $containerColumnConfigurationService,
        protected ContainerService $containerService,
        protected UriBuilder $uriBuilder
    ) {
    }

    public function getNewContentUrlAfterChild(PageLayoutContext $context, Container $container, int $columnNumber, int $recordUid, ?array $defVals): string
    {
        if ($defVals !== null) {
            return $this->getNewContentEditUrl($container, $columnNumber, -$recordUid, $defVals);
        }
        return $this->getNewContentWizardUrl($context, $container, $columnNumber, -$recordUid);
    }

    public function getNewContentUrlAtTopOfColumn(PageLayoutContext $context, Container $container, int $columnNumber, ?array $defVals): ?string
    {
        if ($this->containerColumnConfigurationService->isMaxitemsReached($container, $columnNumber)) {
            return null;
        }
        $newContentElementAtTopTarget = $this->containerService->getNewContentElementAtTopTargetInColumn($container, $columnNumber);
        if ($defVals !== null) {
            return $this->getNewContentEditUrl($container, $columnNumber, $newContentElementAtTopTarget, $defVals);
        }
        return $this->getNewContentWizardUrl($context, $container, $columnNumber, $newContentElementAtTopTarget);
    }

    protected function getNewContentEditUrl(Container $container, int $columnNumber, int $target, array $defVals): string
    {
        $ttContentDefVals = array_merge($defVals, [
            'colPos' => $columnNumber,
            'sys_language_uid' => $container->getLanguage(),
            'tx_container_parent' => $container->getUidOfLiveWorkspace(),
        ]);
        $urlParameters = [
            'edit' => [
                'tt_content' => [
                    $target => 'new',
                ],
            ],
            'defVals' => [
                'tt_content' => $ttContentDefVals,
            ],
            'returnUrl' => $this->getReturnUrl(),
        ];
        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $urlParameters);
    }

    protected function getNewContentWizardUrl(PageLayoutContext $context, Container $container, int $columnNumber, int $uidPid): string
    {
        $pageId = $context->getPageId();
        $urlParameters = [
            'id' => $pageId,
            'sys_language_uid' => $container->getLanguage(),
            'colPos' => $columnNumber,
            'tx_container_parent' => $container->getUidOfLiveWorkspace(),
            'uid_pid' => $uidPid,
            'returnUrl' => $this->getReturnUrl(),
        ];
        return (string)$this->uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    protected function getReturnUrl(): string
    {
        $request = $this->getServerRequest();
        if ($request === null) {
            return '';
        }
        return (string)$request->getAttribute('normalizedParams')->getRequestUri();
    }
}
