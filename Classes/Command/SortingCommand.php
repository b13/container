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
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SortingCommand extends Command
{

    /**
     * @var Sorting
     */
    protected $sorting;

    protected function configure()
    {
        $this->addArgument('dryrun', InputArgument::OPTIONAL, 'do not execute queries', true);
    }

    public function __construct(string $name = null, Sorting $sorting = null)
    {
        parent::__construct($name);
        $this->sorting = $sorting ?? GeneralUtility::makeInstance(Sorting::class);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryrun = (bool)$input->getArgument('dryrun');
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
