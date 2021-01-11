<?php

namespace B13\Container\ViewHelpers;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\View\ContainerLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class DrawChildrenViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @param int $uid
     * @param int $colPos
     * @return string
     */
    public static function render($uid, $colPos)
    {
        $containerLayoutView = GeneralUtility::makeInstance(ContainerLayoutView::class);
        $content = $containerLayoutView->renderContainerChildren((int)$uid, (int)$colPos);
        return $content;
    }
}
