<?php

namespace B13\Container\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IntegrityCommand extends Command
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 2,
                        'update' => [
                            'colPos' => 0
                        ]
                    ]
                ]
            ]
        ];
        Bootstrap::initializeBackendAuthentication();
        $datahander = GeneralUtility::makeInstance(DataHandler::class);
        #$adminUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        #$adminUser->isAdmin()
        $datahander->start([], $cmdmap);
        $datahander->process_cmdmap();
    }

}
