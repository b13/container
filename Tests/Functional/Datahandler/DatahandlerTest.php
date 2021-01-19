<?php

namespace B13\Container\Tests\Functional\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class DatahandlerTest extends FunctionalTestCase
{
    /**
     * @var DataHandler
     */
    protected $dataHandler;

    /**
     * @var BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];


    protected function setUp()
    {
        parent::setUp();
        Bootstrap::initializeLanguageObject();
        $this->backendUser = $this->setUpBackendUserFromFixture(1);
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param string $field
     * @param int $id
     * @return array
     */
    protected function fetchOneRecord($field, $id)
    {
        $row = $this->getDatabase()
            ->exec_SELECTgetSingleRow(
                '*',
                'tt_content',
                $field . '=' . (int)$id
            );
        self::assertTrue(is_array($row));
        return $this->parseDatabaseResultToInt($row);
    }

    protected function parseDatabaseResultToInt(array $row)
    {
        $integerKeys = ['deleted', 'hidden', 'pid', 'uid', 'tx_container_parent', 'colPos', 'sys_language_uid', 'l18n_parent'];
        foreach ($integerKeys as $key) {
            $row[$key] = (int)$row[$key];
        }
        return $row;
    }
}
