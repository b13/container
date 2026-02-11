<?php

declare(strict_types=1);

namespace B13\Container\Backend\ContextMenu;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordContextMenuItemProvider extends \TYPO3\CMS\Backend\ContextMenu\ItemProviders\RecordProvider
{
    /**
     * Add tx_container_parent to newContentElementWizard Url if it is a tt_content record in a container
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $attributes = parent::getAdditionalAttributes($itemName);
        if ($itemName === 'newWizard' && $this->table === 'tt_content'
            && isset($this->record['tx_container_parent']) && $this->record['tx_container_parent'] > 0) {
            $languageField = $this->getLanguageField();
            $urlParameters = [
                'id' => $this->record['pid'],
                'sys_language_uid' => $this->record[$languageField] ?? null,
                'colPos' => $this->record['colPos'],
                'uid_pid' => -$this->record['uid'],
                'tx_container_parent' => $this->record['tx_container_parent'],
            ];
            if ((new Typo3Version())->getMajorVersion() < 14) {
                $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                $url = (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
            } else {
                $url = (string)$this->uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
            }
            if (isset($attributes['data-new-wizard-url'])) {
                $attributes['data-new-wizard-url'] = $url;
            }
        }

        return $attributes;
    }
}
