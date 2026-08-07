<?php

declare(strict_types=1);

namespace TYPO3Tests\DatasetImport\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

#[AsCommand('dataset:empty-db')]
class EmptyDbCommand extends Command
{
    public function __construct(private readonly ConnectionPool $connectionPool, ?string $name = null, ?callable $code = null)
    {
        parent::__construct($name, $code);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->connectionPool->getConnectionForTable('be_users');
        $connection->truncate('be_users');
        return Command::SUCCESS;
    }
}
