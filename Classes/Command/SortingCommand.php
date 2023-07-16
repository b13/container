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

use B13\Container\Integrity\Sorting;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;

class SortingCommand extends Command
{
    /**
     * @var Sorting
     */
    protected $sorting;

    protected function configure()
    {
        $this->addArgument('dryrun', InputArgument::OPTIONAL, 'do not execute queries', true);
        $this->addOption('apply', null, InputOption::VALUE_NONE, 'apply migration');
    }

    public function __construct(Sorting $sorting, string $name = null)
    {
        parent::__construct($name);
        $this->sorting = $sorting;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryrun = (bool)$input->getArgument('dryrun');
        if ($input->getOption('apply') === true) {
            $dryrun = false;
        }
        Bootstrap::initializeBackendAuthentication();
        Bootstrap::initializeLanguageObject();
        $errors = $this->sorting->run($dryrun);
        foreach ($errors as $error) {
            $output->writeln($error);
        }
        if (empty($errors)) {
            $output->writeln('migration finished');
        }
        return 0;
    }
}
