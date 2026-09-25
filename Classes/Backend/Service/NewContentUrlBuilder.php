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
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\StringUtility;

class NewContentUrlBuilder
{
    public function __construct(
        protected Registry $tcaRegistry,
        protected ContainerColumnConfigurationService $containerColumnConfigurationService,
        protected ContainerService $containerService,
        protected UriBuilder $uriBuilder
    ) {
    }

    /**
     * @param array|null $defVals not evaluated anymore, kept for backwards compatibility
     */
    public function getNewContentUrlAfterChild(PageLayoutContext $context, Container $container, int $columnNumber, int $recordUid, ?array $defVals = null): string
    {
        $cType = $this->getSingleAllowedCType($container, $columnNumber);
        if ($cType !== null) {
            return $this->getNewContentEditUrl($container, $columnNumber, -$recordUid, $cType);
        }
        return $this->getNewContentWizardUrl($context, $container, $columnNumber, -$recordUid);
    }

    /**
     * @param array|null $defVals not evaluated anymore, kept for backwards compatibility
     */
    public function getNewContentUrlAtTopOfColumn(PageLayoutContext $context, Container $container, int $columnNumber, ?array $defVals = null): ?string
    {
        if ($this->containerColumnConfigurationService->isMaxitemsReached($container, $columnNumber)) {
            return null;
        }
        $newContentElementAtTopTarget = $this->containerService->getNewContentElementAtTopTargetInColumn($container, $columnNumber);
        $cType = $this->getSingleAllowedCType($container, $columnNumber);
        if ($cType !== null) {
            return $this->getNewContentEditUrl($container, $columnNumber, $newContentElementAtTopTarget, $cType);
        }
        return $this->getNewContentWizardUrl($context, $container, $columnNumber, $newContentElementAtTopTarget);
    }

    public function isNewContentElementWizardSkipped(Container $container, int $columnNumber): bool
    {
        return $this->getSingleAllowedCType($container, $columnNumber) !== null;
    }

    protected function getSingleAllowedCType(Container $container, int $columnNumber): ?string
    {
        $allowedCTypes = (array)$this->tcaRegistry->getAllowedCTypesInColumn($container->getCType(), $columnNumber);
        if (count($allowedCTypes) === 1) {
            return (string)$allowedCTypes[0];
        }
        return null;
    }

    protected function getNewContentEditUrl(Container $container, int $columnNumber, int $target, string $cType): string
    {
        $creationOptions = $this->tcaRegistry->getCreationOptions($cType);
        $ttContentDefVals = array_replace($this->getCreationOptionsDefaultValues($creationOptions), [
            'CType' => $cType,
            'colPos' => $columnNumber,
            'sys_language_uid' => $container->getLanguage(),
            'tx_container_parent' => $container->getUidOfLiveWorkspace(),
        ]);
        if ((bool)($creationOptions['saveAndClose'] ?? false)) {
            // same as core NewContentElementController: skip FormEngine and create the record directly
            $urlParameters = [
                'data' => [
                    'tt_content' => [
                        StringUtility::getUniqueId('NEW') => array_replace($ttContentDefVals, ['pid' => $target]),
                    ],
                ],
                'redirect' => $this->getReturnUrl(),
            ];
            return (string)$this->uriBuilder->buildUriFromRoute('tce_db', $urlParameters);
        }
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

    protected function getCreationOptionsDefaultValues(array $creationOptions): array
    {
        $defaultValues = (array)($creationOptions['defaultValues'] ?? []);
        foreach ($defaultValues as $fieldName => $value) {
            if (is_string($value) && str_starts_with($value, 'LLL:')) {
                $defaultValues[$fieldName] = $this->getLanguageService()->sL($value);
            }
        }
        return $defaultValues;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
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
