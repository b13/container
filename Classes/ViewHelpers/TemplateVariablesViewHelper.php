<?php
namespace B13\Container\ViewHelpers;


use B13\Container\Domain\Factory\Exception;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use B13\Container\Domain\Factory\ContainerFactory;

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

        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $tcaRegistry = GeneralUtility::makeInstance(Registry::class);
        try {
            $container = $containerFactory->buildContainer((int)$arguments['uid']);
            $cType = $container->getCType();
            $grid = $tcaRegistry->getGrid($cType);
            $templateVariableContainer->add('grid', $grid);
        } catch (Exception $e) {

        }

        $output = $renderChildrenClosure();
        $templateVariableContainer->remove('containerConfiguration');
        return $output;
    }
}
