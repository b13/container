<?php

namespace  B13\Container\Hooks;


use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Database;
use B13\Container\Tca\Registry;

class UsedRecords
{

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;

    /**
     * @var Database
     */
    protected $database = null;

    /**
     * ContainerLayoutView constructor.
     * @param Database $database
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);

    }

    /**
     * @param array $params
     * @param PageLayoutView $pageLayoutView
     * @return bool
     */
    public function addContainerChilds(array $params, PageLayoutView $pageLayoutView): bool
    {
        $record = $params['record'];
        if ($record['tx_container_parent'] > 0) {

            #$container = $this->database->fetchOneRecord($record['tx_container_parent']);
            #$columns = $this->tcaRegistry->getAvaiableColumns($container['cType']);
            #foreach ($columns as $column) {
            #    if ($column['colPos'] === $record['colPos']) {
                    return true;
            #    }
            #}
        } else {
            return $params['used'];
        }

    }
}
