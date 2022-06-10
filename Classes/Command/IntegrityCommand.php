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

use B13\Container\Integrity\Integrity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IntegrityCommand extends Command
{
    /**
     * @var Integrity
     */
    protected $integrity;

    public function __construct(Integrity $integrity, string $name = null)
    {
        $this->integrity = $integrity;
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $res = $this->integrity->run();
        if (count($res['errors']) > 0) {
            $output->writeln('ERRORS');
            foreach ($res['errors'] as $error) {
                $output->writeln($error->getErrorMessage());
            }
        }
        if (count($res['warnings']) > 0) {
            $output->writeln('WARNINGS ("unused elements")');
            foreach ($res['warnings'] as $error) {
                $output->writeln($error->getErrorMessage());
            }
        }
        if (count($res['warnings']) === 0 && count($res['errors']) === 0) {
            $output->writeln('Good Job, no ERRORS/WARNINGS');
        }
        return 0;
    }
}
