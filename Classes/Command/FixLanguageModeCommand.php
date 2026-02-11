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

use B13\Container\Integrity\Error\WrongL18nParentError;
use B13\Container\Integrity\Integrity;
use B13\Container\Integrity\IntegrityFix;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'container:fixLanguageMode',
    description: 'connect children of connected container if possible, else disconnect container'
)]
class FixLanguageModeCommand extends Command
{
    public function __construct(protected Integrity $integrity, protected IntegrityFix $integrityFix, ?string $name = null)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $res = $this->integrity->run();
        $errors = [];
        foreach ($res['errors'] as $error) {
            if ($error instanceof WrongL18nParentError) {
                $errors[] = $error;
            }
        }
        $this->integrityFix->languageMode($errors);
        return Command::SUCCESS;
    }
}
