<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractContentDefender extends AbstractDatahandler
{
    protected function setUp(): void
    {
        parent::setUp();
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 12) {
            // content_defender calls FormDataCompiler which wants access global variable TYPO3_REQUEST
            $GLOBALS['TYPO3_REQUEST'] = null;
        } elseif ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 12) {
            // content_defender always returns true for restrictions if global variable TYPO3_REQUEST is null
            $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        }
    }
}
