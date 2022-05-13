<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Backend\Grid;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Backend\Grid\ContainerGridColumnItem;
use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ContainerGridColumnItemTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
    ];

    /**
     * @test
     */
    public function getNewContentUrlContainsUidOfLiveWorkspaceAsContainerParent(): void
    {
        $container = new Container(['uid' => 2, 't3ver_oid' => 1], []);
        $pageLayoutContext = $this->prophesize(PageLayoutContext::class);
        $pageLayoutContext->getPageId()->willReturn(3);
        $gridColumn = $this->prophesize(GridColumn::class);
        $containerGridColumnItem = $this->getAccessibleMock(ContainerGridColumnItem::class, ['foo'], [], '', false);
        $containerGridColumnItem->_set('container', $container);
        $containerGridColumnItem->_set('context', $pageLayoutContext->reveal());
        $containerGridColumnItem->_set('column', $gridColumn->reveal());
        $containerGridColumnItem->_set('record', ['uid' => 4]);
        $newContentUrl = $containerGridColumnItem->getNewContentAfterUrl();
        self::assertStringContainsString('tx_container_parent=1', $newContentUrl, 'should container uid of live workspace record');
    }
}
