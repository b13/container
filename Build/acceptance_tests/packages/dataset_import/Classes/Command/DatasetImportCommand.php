<?php

declare(strict_types=1);

namespace TYPO3Tests\DatasetImport\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\DataHandling\DataSet;

#[AsCommand('dataset:import', 'Import Dataset')]
class DatasetImportCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to CSV dataset to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        DataSet::import($input->getArgument('path'));
        return Command::SUCCESS;
    }
}
