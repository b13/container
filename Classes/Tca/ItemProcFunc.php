<?php

namespace B13\Container\Tca;


use TYPO3\CMS\Backend\View\BackendLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Database;

class ItemProcFunc
{

    /**
     * @var Database
     */
    protected $database = null;

    /**
     * @var BackendLayoutView
     */
    protected $backendLayoutView = null;

    /**
     * ContainerLayoutView constructor.
     * @param Database $database
     */
    public function __construct(Database $database = null, BackendLayoutView $backendLayoutView = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->backendLayoutView = $backendLayoutView ?? GeneralUtility::makeInstance(BackendLayoutView::class);
    }

    /**
     * Gets colPos items to be shown in the forms engine.
     * This method is called as "itemsProcFunc" with the accordant context
     * for tt_content.colPos.
     *
     * @param array $parameters
     */
    public function colPos(array $parameters): void
    {
        $row = $parameters['row'];
        if ($row['tx_container_parent'] > 0) {
            $containerRecord = $this->database->fetchOneRecord($row['tx_container_parent']);
            $grid = $GLOBALS['TCA']['tt_content']['containerConfiguration'][$containerRecord['CType']]['grid'];
            if (is_array($grid)) {
                $items = [];
                foreach ($grid as $rows) {
                    foreach ($rows as $column) {
                        $items[] = [
                            $column['name'],
                            $column['colPos']
                        ];
                    }
                }
                $parameters['items'] = $items;
                return;
            }
        }

        $this->backendLayoutView->colPosListItemProcFunc($parameters);
    }

    /**
     * @param array $parameters
     */
    public function txContainerParent(array $parameters): void
    {
        $row = $parameters['row'];
        $items = [];
        if ($row['tx_container_parent'] > 0) {
            $containerRecord = $this->database->fetchOneRecord($row['tx_container_parent']);
            $items[] = [
                $containerRecord['CType'],
                $containerRecord['uid']
            ];
        } else {
            $items[] = [
                '-',
                0
            ];
        }
        $parameters['items'] = $items;
    }

}
