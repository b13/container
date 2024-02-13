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
        $this->addArgument('pid', InputArgument::OPTIONAL, 'limit to this pid', 0);
        $this->addOption('apply', null, InputOption::VALUE_NONE, 'apply migration');
        $this->addOption(
            'enable-logging',
            null,
            InputOption::VALUE_NONE,
            'enables datahandler logging, should only use for debug issues, not in production'
        );
    }

    public function __construct(Sorting $sorting, string $name = null)
    {
        parent::__construct($name);
        $this->sorting = $sorting;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryrun = $input->getOption('apply') !== true;
        $pid = (int)$input->getArgument('pid');

        Bootstrap::initializeBackendAuthentication();
        Bootstrap::initializeLanguageObject();
        $errors = $this->sorting->run(
            $dryrun,
            $input->getOption('enable-logging'),
            $pid
        );
        foreach ($errors as $error) {
            $output->writeln($error);
        }
        if (empty($errors)) {
            $output->writeln('migration finished');
        }
        return 0;
    }
}
