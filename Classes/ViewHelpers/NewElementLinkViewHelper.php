<?php
namespace B13\Container\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;

class NewElementLinkViewHelper extends AbstractViewHelper
{

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
        $this->registerArgument('containerRecord', 'array', 'Container Record to for the new Element Link', true);
        $this->registerArgument('colPos', 'int', 'colPos Container Record', true);
    }

    public function render(): string
    {
        $containerRecord = $this->arguments['containerRecord'];
        $colPos = $this->arguments['colPos'];

        $urlParameters = [
            'id'                         => $containerRecord['pid'],
            'sys_language_uid'           => 0,
            'colPos'                     => $colPos,
            'tx_container_parent' => $containerRecord['uid'],
            'uid_pid'                    => $containerRecord['pid'],
            'returnUrl'                  => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('new_content_element_wizard', $urlParameters);

        return '<a class="btn btn-default btn-sm t3js-toggle-new-content-element-wizard" href="' . $url . '">new</a>';
    }
}
