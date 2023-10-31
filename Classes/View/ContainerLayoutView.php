<?php

namespace B13\Container\View;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Grid\ContainerGridColumn;
use B13\Container\ContentDefender\ContainerColumnConfigurationService;
use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use B13\Container\Domain\Model\Container;
use B13\Container\Domain\Service\ContainerService;
use B13\Container\Tca\Registry;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

class ContainerLayoutView extends PageLayoutView
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ContainerColumnConfigurationService
     */
    protected $containerColumnConfigurationService;

    /**
     * @var ContainerService
     */
    protected $containerService;

    /**
     * variable and calls can be dropped on v10
     * @var int
     */
    public $counter = 0;

    /**
     * variable and calls can be dropped on v10
     * @var int
     */
    public $nextThree = 3;

    /**
     * ContainerLayoutView constructor.
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param ContainerFactory|null $containerFactory
     * @param Registry|null $registry
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher = null,
        ContainerFactory $containerFactory = null,
        Registry $registry = null,
        ContainerColumnConfigurationService $containerColumnConfigurationService = null,
        ContainerService $containerService = null
    ) {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
        $this->registry = $registry ?? GeneralUtility::makeInstance(Registry::class);
        $this->containerColumnConfigurationService = $containerColumnConfigurationService ?? GeneralUtility::makeInstance(ContainerColumnConfigurationService::class);
        $this->containerService = $containerService ?? GeneralUtility::makeInstance(ContainerService::class);

        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() === 10) {
            parent::__construct($eventDispatcher);
        } else {
            parent::__construct();
        }
    }

    /**
     * @param int $uid
     * @param int $colPos
     * @return string
     */
    public function renderContainerChildren(int $uid, int $colPos): string
    {
        $this->initWebLayoutModuleData();
        $this->initLabels();

        try {
            $container = $this->containerFactory->buildContainer($uid);
        } catch (\B13\Container\Domain\Factory\Exception $e) {
            return '';
        }
        $this->id = $container->getPid();
        $this->pageinfo = BackendUtility::readPageAccess($this->id, '');
        $this->container = $container;
        $content = $this->renderRecords($colPos);
        return $content;
    }

    protected function initLabels(): void
    {
        $this->CType_labels = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
            if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() >= 12) {
                $this->CType_labels[$val['value'] ?? $val[1]] = $this->getLanguageService()->sL($val['label'] ?? $val[0]);
            } else {
                $this->CType_labels[$val[1]] = $this->getLanguageService()->sL($val[0]);
            }
        }

        $this->itemLabels = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
            $this->itemLabels[$name] = $this->getLanguageService()->sL($val['label']);
        }
    }

    /**
     * @param int $colPos
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function buildNewContentElementWizardLinkTop(int $colPos): string
    {
        $target = $this->containerService->getNewContentElementAtTopTargetInColumn($this->container, $colPos);
        $urlParameters = [
            'id' => $this->container->getPid(),
            'sys_language_uid' => $this->container->getLanguage(),
            'tx_container_parent' => $this->container->getUidOfLiveWorkspace(),
            'colPos' => $colPos,
            'uid_pid' => $target,
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
        return $url;
    }

    /**
     * @param array $currentRecord
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function buildNewContentElementWizardLinkAfterCurrent(array $currentRecord): string
    {
        $containerRecord = $this->container->getContainerRecord();
        $colPos = $currentRecord['colPos'];
        $target = -$currentRecord['uid'];
        $lang = $currentRecord['sys_language_uid'];
        $urlParameters = [
            'id' => $containerRecord['pid'],
            'sys_language_uid' => $lang,
            'colPos' => $colPos,
            'tx_container_parent' => $this->container->getUidOfLiveWorkspace(),
            'uid_pid' => $target,
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
        return $url;
    }

    protected function initWebLayoutModuleData(): void
    {
        $webLayoutModuleData = BackendUtility::getModuleData([], [], 'web_layout');
        if (isset($webLayoutModuleData['tt_content_showHidden'])) {
            $this->tt_contentConfig['showHidden'] = $webLayoutModuleData['tt_content_showHidden'];
        }
    }

    /**
     * Creates the icon image tag for record from table and wraps it in a link which will trigger the click menu.
     *
     * @param string $table Table name
     * @param array $row Record array
     * @return string HTML for the icon
     */
    public function getIcon($table, $row)
    {
        if ($this->isLanguageEditable()) {
            return parent::getIcon($table, $row);
        }
        $toolTip = BackendUtility::getRecordToolTip($row, 'tt_content');
        $icon = '<span ' . $toolTip . '>' . $this->iconFactory->getIconForRecord($table, $row, Icon::SIZE_SMALL)->render() . '</span>';
        $this->counter++;
        // do not render click-menu
        return $icon;
    }

    /**
     * @return bool
     */
    protected function isLanguageEditable(): bool
    {
        return $this->container->getLanguage() === 0 || !$this->container->isConnectedMode();
    }

    /**
     * @param int $colPos
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function renderNewContentButtonAtTop(int $colPos): string
    {
        // Add new content at the top most position
        $link = '';
        $content = '';
        if ($this->isContentEditable() && $this->isLanguageEditable()) {
            $url = $this->buildNewContentElementWizardLinkTop($colPos);
            $title = htmlspecialchars($this->getLanguageService()->getLL('newContentElement'));
            $link = '<a href="' . htmlspecialchars($url) . '" '
                . 'title="' . $title . '"'
                . 'data-title="' . $title . '"'
                . 'class="btn btn-default btn-sm t3js-toggle-new-content-element-wizard">'
                . $this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL)->render()
                . ' '
                . htmlspecialchars($this->getLanguageService()->getLL('content')) . '</a>';
        }

        if ($this->getBackendUser()->checkLanguageAccess($this->container->getLanguage())) {
            $content = '
                <div class="t3-page-ce t3js-page-ce" data-page="' . $this->container->getPid() . '" id="' . StringUtility::getUniqueId() . '">
                    <div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="colpos-' . $colPos . '-page-' . $this->container->getUid() . ContainerGridColumn::CONTAINER_COL_POS_DELIMITER . StringUtility::getUniqueId() . '">'
                . $link
                . '</div>
                    <div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available"></div>
                </div>
                ';
        }
        return $content;
    }

    /**
     * @param array $row
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function renderNewContentButtonAfterContentElement(array $row): string
    {
        $url = $this->buildNewContentElementWizardLinkAfterCurrent($row);
        $title = htmlspecialchars($this->getLanguageService()->getLL('newContentElement'));
        return  '<a href="' . htmlspecialchars($url) . '" '
            . 'title="' . $title . '"'
            . 'data-title="' . $title . '"'
            . 'class="btn btn-default btn-sm t3js-toggle-new-content-element-wizard">'
            . $this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL)->render()
            . ' '
            . htmlspecialchars($this->getLanguageService()->getLL('content')) . '</a>';
    }

    /**
     * @param int $colPos
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    protected function renderRecords(int $colPos): string
    {
        $containerRecord = $this->container->getContainerRecord();
        $this->resolveSiteLanguages((int)$containerRecord['pid']);
        $records = $this->container->getChildrenByColPos($colPos);
        $this->nextThree = 1;
        $this->generateTtContentDataArray($records);
        $allowNewContentElements = true;
        if ($this->containerColumnConfigurationService->isMaxitemsReached($this->container, $colPos)) {
            $allowNewContentElements = false;
        }
        $content = '';
        $head = '';
        $currentLanguage = $containerRecord['sys_language_uid'];
        $id = $containerRecord['pid'];

        // Start wrapping div
        $content .= '<div data-colpos="' . $containerRecord['uid'] . ContainerGridColumn::CONTAINER_COL_POS_DELIMITER . $colPos . '" data-language-uid="' . $currentLanguage . '" class="t3js-sortable t3js-sortable-lang t3js-sortable-lang-' . $currentLanguage . ' t3-page-ce-wrapper">';
        if ($allowNewContentElements) {
            $content .= $this->renderNewContentButtonAtTop($colPos);
        }

        foreach ($records as $row) {
            if (is_array($row) && !VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
                $singleElementHTML = '<div class="t3-page-ce-dragitem" id="' . StringUtility::getUniqueId() . '">';
                // new is visible ... s. ContextMenuController
                $disableMoveAndNewButtons = !$this->isLanguageEditable();
                $singleElementHTML .= $this->tt_content_drawHeader(
                    $row,
                    $this->tt_contentConfig['showInfo'] ? 15 : 5,
                    $disableMoveAndNewButtons,
                    true,
                    $this->getBackendUser()->doesUserHaveAccess($this->pageinfo, Permission::CONTENT_EDIT)
                );

                $innerContent = '<div ' . ($row['_ORIG_uid'] ? ' class="ver-element"' : '') . '>'
                    . $this->tt_content_drawItem($row) . '</div>';
                $singleElementHTML .= '<div class="t3-page-ce-body-inner">' . $innerContent . '</div></div>'
                    . $this->tt_content_drawFooter($row);
                $isDisabled = $this->isDisabled('tt_content', $row);
                $statusHidden = $isDisabled ? ' t3-page-ce-hidden t3js-hidden-record' : '';
                $displayNone = !$this->tt_contentConfig['showHidden'] && $isDisabled ? ' style="display: none;"' : '';

                $singleElementHTML = '<div class="t3-page-ce t3js-page-ce t3js-page-ce-sortable ' . $statusHidden . '" id="element-tt_content-'
                    . $row['uid'] . '" data-table="tt_content" data-uid="' . $row['uid'] . '"' . $displayNone . '>' . $singleElementHTML . '</div>';

                $singleElementHTML .= '<div class="t3-page-ce" data-colpos="' . $containerRecord['uid'] . ContainerGridColumn::CONTAINER_COL_POS_DELIMITER . $colPos . '">';
                $singleElementHTML .= '<div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="colpos-' . $colPos . '-page-' . $id .
                    ContainerGridColumn::CONTAINER_COL_POS_DELIMITER . StringUtility::getUniqueId() . '">';
                // Add icon "new content element below"
                if (!$disableMoveAndNewButtons
                    && $this->isContentEditable()
                    && $allowNewContentElements
                    && $this->getBackendUser()->checkLanguageAccess($currentLanguage)
                ) {
                    $singleElementHTML .= $this->renderNewContentButtonAfterContentElement($row);
                }
                if ($allowNewContentElements) {
                    $singleElementHTML .= '</div></div><div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available"></div></div>';
                }
                $content .= $singleElementHTML;
            }
        }
        $content .= '</div>';
        $colTitle = $this->getLanguageService()->sL($this->registry->getColPosName($this->container->getCType(), (int)$colPos));
        $head .= $this->tt_content_drawColHeader($colTitle);

        return $head . $content;
    }
}
