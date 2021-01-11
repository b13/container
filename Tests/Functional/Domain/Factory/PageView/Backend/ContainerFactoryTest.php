<?php

namespace B13\Container\Tests\Functional\Domain\Factory\PageView\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\PageView\Backend\ContainerFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;

class ContainerFactoryTest extends FunctionalTestCase
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
    public function localizedContainerChildElementsHasSortingOfDefaultChildElements()
    {
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Domain/Factory/Fixture/localizedContainerChildElementsHasSortingOfDefaultChildElements.xml');
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer(2);
        $children = $container->getChildrenByColPos(201);
        self::assertSame(2, count($children));
        $first = $children[0];
        self::assertSame(6, (int)$first['uid']);
    }

}
