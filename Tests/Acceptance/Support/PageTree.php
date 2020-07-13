<?php

declare(strict_types = 1);
namespace B13\Container\Tests\Acceptance\Support;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\TestingFramework\Core\Acceptance\Helper\AbstractPageTree;

class PageTree extends AbstractPageTree
{
    /**
     * Inject our core AcceptanceTester actor into ModalDialog
     *
     * @param BackendTester $I
     */
    public function __construct(BackendTester $I)
    {
        $this->tester = $I;
    }
}
