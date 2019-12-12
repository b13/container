<?php
namespace B13\Container\ViewHelpers;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use B13\Container\Database;

class TemplateVariablesViewHelper extends AbstractViewHelper
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
        $database = GeneralUtility::makeInstance(Database::class);;
        $record = $database->fetchOneRecord((int)$arguments['uid']);
        $templateVariableContainer->add('containerRecord', $record);
        if (!empty($GLOBALS['TCA']['tt_content']['containerConfiguration'][$record['CType']])) {
            $templateVariableContainer->add('containerConfiguration', $GLOBALS['TCA']['tt_content']['containerConfiguration'][$record['CType']]);
        }

        $output = $renderChildrenClosure();
        $templateVariableContainer->remove('containerRecord');
        $templateVariableContainer->remove('containerConfiguration');
        return $output;
    }
}
