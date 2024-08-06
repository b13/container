<?php

declare(strict_types=1);

namespace B13\Container\Command;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Integrity\Error\ChildInTranslatedContainerError;
use B13\Container\Integrity\Integrity;
use B13\Container\Integrity\IntegrityFix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixContainerParentForConnectedModeCommand extends Command
{
    /**
     * @var Integrity
     */
    protected $integrity;

    /**
     * @var IntegrityFix
     */
    protected $integrityFix;

    public function __construct(Integrity $integrity, IntegrityFix $integrityFix, ?string $name = null)
    {
        $this->integrity = $integrity;
        $this->integrityFix = $integrityFix;
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $res = $this->integrity->run();
        foreach ($res['errors'] as $error) {
            if ($error instanceof ChildInTranslatedContainerError) {
                $this->integrityFix->changeContainerParentToDefaultLanguageContainer($error);
            }
        }
        return 0;
    }
}
