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
        if ($record === false) {
            return null;
        }
        return $record;
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
        if ($record === false) {
            return null;
        }
        return $record;
    }

    /**
     * @param array $record
     * @return array
     */
    public function fetchOverlayRecords(array $record)
    {
        return (array)$this->getDatabase()
            ->exec_SELECTgetRows(
                '*',
                'tt_content',
                'l18n_parent=' . $record['uid'] . $this->getAdditionalWhereClause()
            );
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
                'l10n_source=' . (int)$uid . ' AND sys_language_uid=' . (int)$language . $this->getAdditionalWhereClause()
            );
        if ($record === false) {
            return null;
        }
        return $record;
    }

    /**
     * @param int $parent
     * @param int $language
     * @return array
     */
    public function fetchRecordsByParentAndLanguage($parent, $language)
    {

        return (array)$this->getDatabase()
            ->exec_SELECTgetRows(
                '*',
                'tt_content',
                'tx_container_parent=' . (int)$parent . ' AND sys_language_uid=' . (int)$language . $this->getAdditionalWhereClause(),
                '',
                'sorting ASC'
            );
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
        if ($record === false) {
            return null;
        }
        return $record;
    }
}
