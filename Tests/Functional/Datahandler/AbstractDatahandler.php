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
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractDatahandler extends FunctionalTestCase
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
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    /**
     * @var non-empty-string[]
     */
    protected array $coreExtensionsToLoad = ['workspaces'];

    protected function linkSiteConfigurationIntoTestInstance(): void
    {
        $from = ORIGINAL_ROOT . '../../Build/sites';
        $to = $this->getInstancePath() . '/typo3conf/sites';
        if (!is_dir($from)) {
            throw new \Exception('site config directory not found', 1630425034);
        }
        if (!file_exists($to)) {
            $success = symlink(realpath($from), $to);
            if ($success === false) {
                throw new \Exception('cannot link site config', 1630425035);
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkSiteConfigurationIntoTestInstance();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->backendUser = $GLOBALS['BE_USER'] = $this->setUpBackendUser(1);
        $GLOBALS['BE_USER'] = $this->backendUser;
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }
}
