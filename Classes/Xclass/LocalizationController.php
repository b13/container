<?php

namespace B13\Container\Xclass;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    /**
     * LocalizationController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->tcaRegistry = GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @param int $pageId
     * @return array
     */
    protected function getPageColumns(int $pageId): array
    {
        return parent::getPageColumns($pageId);
        $pagesColumns = parent::getPageColumns($pageId);
        $gridColumns = $this->tcaRegistry->getAllAvailableColumns();
        foreach ($gridColumns as $gridColumn) {
            $pagesColumns['columns'][$gridColumn['colPos']] = $gridColumn['name'];
            $pagesColumns['columnList'][] = $gridColumn['colPos'];
        }
        return $pagesColumns;
    }

}
