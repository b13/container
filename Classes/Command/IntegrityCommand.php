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
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle($input, $output);
        $res = $this->integrity->run();
        if (count($res['errors']) > 0) {
            $errors = [];
            foreach ($res['errors'] as $error) {
                $errors[] = $error->getErrorMessage();
            }
            $io->error('ERRORS: ' . chr(10) . implode(chr(10), $errors));
        }
        if (count($res['warnings']) > 0) {
            $warnings = [];
            foreach ($res['warnings'] as $warning) {
                $warnings[] = $warning->getErrorMessage();
            }
            $io->error('WARNINGS ("unused elements")' . chr(10) . implode(chr(10), $warnings));
        }
        if (count($res['warnings']) === 0 && count($res['errors']) === 0) {
            $io->success('Good Job, no errors/warnings!');
        }
        return 0;
    }
}
