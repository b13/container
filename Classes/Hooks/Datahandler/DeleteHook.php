<?php

namespace  B13\Container\Hooks\Datahandler;


use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Domain\Factory\ContainerFactory;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class DeleteHook
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory = null;

    /**
     * @param ContainerFactory|null $containerFactory
     */
    public function __construct(ContainerFactory $containerFactory = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
    }

    /**
     * @param string $table
     * @param int $id
     * @param array $recordToDelete
     * @param bool $recordWasDeleted
     * @param DataHandler $dataHandler
     */
    public function processCmdmap_deleteAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted, DataHandler $dataHandler): void
    {
        if ($table === 'tt_content') {
            try {
                $container = $this->containerFactory->buildContainer($id);
                $childs = $container->getChildRecords();
                $toDelete = [];
                foreach ($childs as $colPos => $record) {
                    $toDelete[$record['uid']] = ['delete' => 1];
                }
                if (count($toDelete) > 0) {
                    $cmd = ['tt_content' => $toDelete];
                    $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                    $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                    $localDataHandler->process_cmdmap();
                }
            } catch (Exception $e) {
                // nothing todo
            }
        }
    }

}
