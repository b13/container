<?php



namespace B13\Container\Tests\Functional\Xclasses;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Xclasses\RecordLocalizeSummaryModifier;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RecordLocalizeSummaryModifierTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    /**
     * @test
     */
    public function getContainerUidsReturnsAllUids()
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Xclasses/Fixtures/two_container_elements.xml');
        $recordLocalizeSummeryModifier = $this->getAccessibleMock(
            RecordLocalizeSummaryModifier::class,
            ['foo']
        );
        $containerUids = $recordLocalizeSummeryModifier->_call('getContainerUids', [1, 2]);
        self::assertSame(2, count($containerUids));
    }
}
