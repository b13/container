<?php

namespace B13\Container;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaContainerItems
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
    }

    /**
     * @param array $parameters
     */
    public function listItemProcFunc(array $parameters): void
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
