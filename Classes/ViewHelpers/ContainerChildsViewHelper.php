<?php
namespace B13\Container\ViewHelpers;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use B13\Container\Database;

class ContainerChildsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uid', 'int', 'Uid of Container Record', true);
        $this->registerArgument('colPos', 'int', 'colPos to fetch', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $database = GeneralUtility::makeInstance(Database::class);
        $records = $database->fetchRecordsByParentAndColPos((int)$arguments['uid'], (int)$arguments['colPos']);
        $templateVariableContainer->add('containerChilds', $records);
        $output = $renderChildrenClosure();
        $templateVariableContainer->remove('containerChilds');
        return $output;
    }
}
