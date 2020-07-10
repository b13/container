<?php

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IntegrityCommand extends Command
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->getName() === 'integrity:run') {
            trigger_error(
                'use "container:integrity" instead of "integrity:run" as command name',
                E_USER_DEPRECATED
            );
        }
        $integrity = GeneralUtility::makeInstance(Integrity::class);
        $res = $integrity->run();
        if (count($res['errors']) > 0) {
            $output->writeln('ERRORS');
            foreach ($res['errors'] as $error) {
                $output->writeln($error->getErrorMessage());
            }
        }
        if (count($res['warnings']) > 0) {
            $output->writeln('WARNINGS');
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
