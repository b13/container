<?php

namespace B13\Container\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Integrity implements SingletonInterface
{

    /**
     * @var Database
     */
    protected $database = null;

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;


    /**
     * ContainerFactory constructor.
     * @param Database|null $database
     * @param Registry|null $tcaRegistry
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    public function run(): array
    {
        $res = [
            'errors' => [],
            'warnings' => []
        ];
        $cTypes = $this->tcaRegistry->getRegisteredCTypes();
        $colPosByCType = [];
        foreach ($cTypes as $cType) {
            $columns = $this->tcaRegistry->getAvaiableColumns($cType);
            $colPosByCType[$cType] = [];
            foreach ($columns as $column) {
                $colPosByCType[$cType][] = $column['colPos'];
            }
        }
        $containerRecords = $this->database->getContainerRecords($cTypes);
        $containerChildRecords = $this->database->getContainerChildRecords();
        foreach ($containerChildRecords as $containerChildRecord) {
            if (empty($containerRecords[$containerChildRecord['tx_container_parent']])) {
                $res['errors'][] = 'container child with uid ' . $containerChildRecord['uid'] .
                    ' has non existing tx_container_parent ' . $containerChildRecord['tx_container_parent'];
            } else {
                $containerRecord = $containerRecords[$containerChildRecord['tx_container_parent']];
                if ($containerRecord['pid'] !== $containerChildRecord['pid']) {
                    $res['errors'][] = 'container child with uid ' . $containerChildRecord['uid'] .
                        ' has pid ' . $containerChildRecord['pid']
                        . ' but tx_container_parent ' . $containerChildRecord['tx_container_parent']
                        . ' has pid ' . $containerRecord['pid'];
                }
                if (!in_array($containerChildRecord['colPos'], $colPosByCType[$containerRecord['CType']])) {
                    $res['warnings'][] = 'container child with uid ' . $containerChildRecord['uid']
                        . ' on page ' . $containerChildRecord['pid'] .
                        ' has invlid colPos ' . $containerChildRecord['colPos']
                        . ' in container ' . $containerChildRecord['tx_container_parent']
                        . ' with CType ' . $containerRecord['CType'];
                }
            }

        }
        return $res;
    }

}
