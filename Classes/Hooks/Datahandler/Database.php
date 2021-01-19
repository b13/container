<?php

namespace B13\Container\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;

class Database implements SingletonInterface
{

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return string
     */
    protected function getAdditionalWhereClause()
    {
        return ' AND deleted=0';
    }

    protected function parseDatabaseResultToInt(array $row)
    {
        $integerKeys = ['deleted', 'hidden', 'pid', 'uid', 'tx_container_parent', 'colPos', 'sys_language_uid', 'l18n_parent'];
        foreach ($integerKeys as $key) {
            $row[$key] = (int)$row[$key];
        }
        return $row;
    }

    /**
     * @param int $uid
     * @return array|null
     */
    public function fetchOneRecord($uid)
    {
        $record = $this->getDatabase()
            ->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                'uid=' . (int)$uid . $this->getAdditionalWhereClause()
            );
        if (!$record) {
            return null;
        }
        return $this->parseDatabaseResultToInt($record);
    }

    /**
     * @param int $uid
     * @return array|null
     */
    public function fetchOneMovedRecord($uid)
    {
        $record = $this->getDatabase()
            ->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                't3ver_move_id=' . (int)$uid . $this->getAdditionalWhereClause()
            );
        if (!$record) {
            return null;
        }
        return $this->parseDatabaseResultToInt($record);
    }

    /**
     * @param array $record
     * @return array
     */
    public function fetchOverlayRecords(array $record)
    {
        $rows = (array)$this->getDatabase()
            ->exec_SELECTgetRows(
                '*',
                'tt_content',
                'l18n_parent=' . $record['uid'] . $this->getAdditionalWhereClause()
            );
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->parseDatabaseResultToInt($row);
        }
        return $records;
    }

    /**
     * @param int $uid
     * @param int $language
     * @return array
     */
    public function fetchOneTranslatedRecord($uid, $language)
    {
        $record = $this->getDatabase()
            ->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                'l18n_parent=' . (int)$uid . ' AND sys_language_uid=' . (int)$language . $this->getAdditionalWhereClause()
            );
        if (!$record) {
            return null;
        }
        return $this->parseDatabaseResultToInt($record);
    }

    /**
     * @param int $parent
     * @param int $language
     * @return array
     */
    public function fetchRecordsByParentAndLanguage($parent, $language)
    {

        $rows = (array)$this->getDatabase()
            ->exec_SELECTgetRows(
                '*',
                'tt_content',
                'tx_container_parent=' . (int)$parent . ' AND sys_language_uid=' . (int)$language . $this->getAdditionalWhereClause(),
                '',
                'sorting ASC'
            );
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->parseDatabaseResultToInt($row);
        }
        return $records;
    }

    /**
     * @param int $defaultUid
     * @param int $language
     * @return array|null
     */
    public function fetchContainerRecordLocalizedFreeMode($defaultUid, $language)
    {
        $record = $this->getDatabase()
            ->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                'l10n_source=' . (int)$defaultUid . ' AND l18n_parent=0 AND sys_language_uid=' . (int)$language . $this->getAdditionalWhereClause()
            );
        if (!$record) {
            return null;
        }
        return $this->parseDatabaseResultToInt($record);
    }
}
