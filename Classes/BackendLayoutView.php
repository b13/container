<?php

namespace B13\Container;


use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
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
     * Gets colPos items to be shown in the forms engine.
     * This method is called as "itemsProcFunc" with the accordant context
     * for tt_content.colPos.
     *
     * @param array $parameters
     */
    public function colPosListItemProcFunc(array $parameters): void
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

        parent::colPosListItemProcFunc($parameters);
    }

}
