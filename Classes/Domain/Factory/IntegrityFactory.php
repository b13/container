<?php

namespace B13\Container\Domain\Factory;

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\Registry;

class IntegrityFactory implements SingletonInterface
{

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;


    /**
     * ContainerFactory constructor.
     * @param Database|null $database
     * @param Registry|null $tcaRegistry
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }


}
