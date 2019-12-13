<?php

namespace B13\Container;

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Versioning\VersionState;


class ContainerLayoutView extends PageLayoutView
{
    /**
     * @var Database
     */
    protected $database = null;

    /**
     * ContainerLayoutView constructor.
     * @param Database $database
     */
    public function __construct(Database $database = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        parent::__construct();
    }


    /**
     * @param int $uid
     * @param int $colPos
     * @return string
     */
    public function renderContainerChilds(int $uid, int $colPos): string
    {
        $records = $this->database->fetchRecordsByParentAndColPosIncludeHidden($uid, $colPos);
        $containerRecord = $this->database->fetchOneRecord($uid);

        $this->nextThree = 1;
        $this->initWebLayoutModuleData();
        $this->generateTtContentDataArray($records);
        $content = $this->renderRecords($records, $colPos, $containerRecord);
        return $content;
    }

    /**
     * @param int $colPos
     * @param array $containerRecord
     * @param int $lang
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function buildNewContentElementWizardLinkTop(int $colPos, array $containerRecord, int $lang = 0): string
    {
        $urlParameters = [
            'id' => $containerRecord['pid'],
            'sys_language_uid' => $lang,
            'tx_container_parent' => $containerRecord['uid'],
            'colPos' => $colPos,
            'uid_pid' => $containerRecord['pid'],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
        return $url;
    }

    /**
     * @param array $currentRecord
     * @param array $containerRecord
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function buildNewContentElementWizardLinkAfterCurrent(array $currentRecord, array $containerRecord): string
    {
        $colPos = $currentRecord['colPos'];
        $target = -$currentRecord['uid'];
        $lang = $currentRecord['sys_language_uid'];
        $urlParameters = [
            'id' => $containerRecord['pid'],
            'sys_language_uid' => $lang,
            'colPos' => $colPos,
            'tx_container_parent' => $containerRecord['uid'],
            'uid_pid' => $target,
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);
        return $url;
    }

    /**
     * @return void
     */
    protected function initWebLayoutModuleData(): void
    {
        $webLayoutModuleData = BackendUtility::getModuleData([], [], 'web_layout');
        if (isset($webLayoutModuleData['tt_content_showHidden'])) {
            $this->tt_contentConfig['showHidden'] = $webLayoutModuleData['tt_content_showHidden'];
        }
    }


    protected function renderRecords(array $recods, int $colPos, array $containerRecord): string
    {
        /**
         * // Setting language list:
        $langList = $this->tt_contentConfig['sys_language_uid'];
        if ($this->tt_contentConfig['languageMode']) {
        if ($this->tt_contentConfig['languageColsPointer']) {
        $langList = '0,' . $this->tt_contentConfig['languageColsPointer'];
        } else {
        $langList = implode(',', array_keys($this->tt_contentConfig['languageCols']));
        }
        $languageColumn = [];
        }
        $langListArr = GeneralUtility::intExplode(',', $langList);
         */
        $content = '';
        $head = '';
        $lP = 0;
        $id = $containerRecord['pid'];

        if (!isset($this->contentElementCache[$lP])) {
            $this->contentElementCache[$lP] = [];
        }

        if (!$lP) {
            $defaultLanguageElementsByColumn[$colPos] = [];
        }

        // Start wrapping div
        $content .= '<div data-colpos="' . $containerRecord['uid'] . '-' . $colPos . '" data-language-uid="' . $lP . '" class="t3js-sortable t3js-sortable-lang t3js-sortable-lang-' . $lP . ' t3-page-ce-wrapper">';
        // Add new content at the top most position
        $link = '';
        if ($this->isContentEditable()
            && (!$this->checkIfTranslationsExistInLanguage($recods, $lP))
        ) {
            $url = $this->buildNewContentElementWizardLinkTop($colPos, $containerRecord, $lP);
            $title = htmlspecialchars($this->getLanguageService()->getLL('newContentElement'));
            $link = '<a href="' . htmlspecialchars($url) . '" '
                . 'title="' . $title . '"'
                . 'data-title="' . $title . '"'
                . 'class="btn btn-default btn-sm t3js-toggle-new-content-element-wizard">'
                . $this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL)->render()
                . ' '
                . htmlspecialchars($this->getLanguageService()->getLL('content')) . '</a>';
        }
        if ($this->getBackendUser()->checkLanguageAccess($lP)) {
            $content .= '
                <div class="t3-page-ce t3js-page-ce" data-page="' . (int)$id . '" id="' . StringUtility::getUniqueId() . '">
                    <div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="colpos-' . $colPos . '-page-' . $id . '-' . StringUtility::getUniqueId() . '">'
                . $link
                . '</div>
                    <div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available"></div>
                </div>
                ';
        }
        $editUidList = '';

        foreach ((array)$recods as $rKey => $row) {
            $this->contentElementCache[$lP][$colPos][$row['uid']] = $row;
            if ($this->tt_contentConfig['languageMode']) {
                $languageColumn[$colPos][$lP] = $head . $content;
            }
            if (is_array($row) && !VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
                $singleElementHTML = '<div class="t3-page-ce-dragitem" id="' . StringUtility::getUniqueId() . '">';
                if (!$lP && ($this->defLangBinding || $row['sys_language_uid'] != -1)) {
                    $defaultLanguageElementsByColumn[$colPos][] = ($row['_ORIG_uid'] ?? $row['uid']);
                }
                $editUidList .= $row['uid'] . ',';
                $disableMoveAndNewButtons = $this->defLangBinding && $lP > 0 && $this->checkIfTranslationsExistInLanguage($recods, $lP);
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
                $highlightHeader = '';
                if ($this->checkIfTranslationsExistInLanguage([], (int)$row['sys_language_uid']) && (int)$row['l18n_parent'] === 0) {
                    $highlightHeader = ' t3-page-ce-danger';
                }
                $singleElementHTML = '<div class="t3-page-ce' . $highlightHeader . ' t3js-page-ce t3js-page-ce-sortable ' . $statusHidden . '" id="element-tt_content-'
                    . $row['uid'] . '" data-table="tt_content" data-uid="' . $row['uid'] . '"' . $displayNone . '>' . $singleElementHTML . '</div>';

                $singleElementHTML .= '<div class="t3-page-ce" data-colpos="' . $containerRecord['uid'] . '-' . $colPos . '">';
                $singleElementHTML .= '<div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="colpos-' . $colPos . '-page-' . $id .
                    '-' . StringUtility::getUniqueId() . '">';
                // Add icon "new content element below"
                if (!$disableMoveAndNewButtons
                    && $this->isContentEditable()
                    && $this->getBackendUser()->checkLanguageAccess($lP)
                    && (!$this->checkIfTranslationsExistInLanguage($recods, $lP))
                ) {
                    $url = $this->buildNewContentElementWizardLinkAfterCurrent($row, $containerRecord);
                    $title = htmlspecialchars($this->getLanguageService()->getLL('newContentElement'));
                    $singleElementHTML .= '<a href="' . htmlspecialchars($url) . '" '
                        . 'title="' . $title . '"'
                        . 'data-title="' . $title . '"'
                        . 'class="btn btn-default btn-sm t3js-toggle-new-content-element-wizard">'
                        . $this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL)->render()
                        . ' '
                        . htmlspecialchars($this->getLanguageService()->getLL('content')) . '</a>';
                }
                $singleElementHTML .= '</div></div><div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available"></div></div>';
                if ($this->defLangBinding && $this->tt_contentConfig['languageMode']) {
                    $defLangBinding[$colPos][$lP][$row[$lP ? 'l18n_parent' : 'uid'] ?: $row['uid']] = $singleElementHTML;
                } else {
                    $content .= $singleElementHTML;
                }
            } else {
                unset($recods[$rKey]);
            }
        }
        $content .= '</div>';
        $colTitle = BackendUtility::getProcessedValue('tt_content', 'colPos', $colPos);
        $head .= $this->tt_content_drawColHeader($colTitle);

        return $head . $content;
    }
}
