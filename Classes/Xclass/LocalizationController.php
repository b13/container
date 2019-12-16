<?php

namespace B13\Container\Xclass;




use B13\Container\TcaRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{

    /**
     * @param int $pageId
     * @return array
     */
    protected function getPageColumns(int $pageId): array
    {
        $pagesColumns = parent::getPageColumns($pageId);
        $registry = GeneralUtility::makeInstance(TcaRegistry::class);
        $gridColumns = $registry->getAllAvailableColumns();
        foreach ($gridColumns as $gridColumn) {
            $pagesColumns['columns'][$gridColumn['colPos']] = $gridColumn['name'];
            $pagesColumns['columnList'][] = $gridColumn['colPos'];
        }
        return $pagesColumns;
    }

}
