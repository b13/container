<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Integrity;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class SortingWithContentDefenderTest extends SortingTest
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
        'typo3conf/ext/content_defender',
    ];

    /**
     * @test
     */
    public function childBeforeContainerIsSortedAfterContainerEvenIfCTypeDisallowedByContentDefender(): void
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Integrity/Fixtures/SortingWithContentDefender/disallowed_child_is_before_container.xml');
        $errors = $this->sorting->run(false);
        self::assertTrue(count($errors) === 1, 'should get one error');
        $rows = $this->getContentsByUid();
        self::assertTrue($rows[3]['sorting'] < $rows[2]['sorting'], 'child should be sorted after container');
    }
}
