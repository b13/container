<?php

namespace B13\Container\Domain\Factory;

use B13\Container\Domain\Model\Container;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\Registry;

class ContainerFactory implements SingletonInterface
{

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Registry
     */
    protected $tcaRegistry = null;


    /**
     * ContainerFactory constructor.
     * @param Database|null $database
     * @param Registry|null $tcaRegistry
     */
    public function __construct(Database $database = null, Registry $tcaRegistry = null)
    {
        $this->database = $database ?? GeneralUtility::makeInstance(Database::class);
        $this->tcaRegistry = $tcaRegistry ?? GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @param int $uid
     * @return Container
     */
    public function buildContainer(int $uid): Container
    {
        $record = $this->database->fetchOneRecord($uid);
        if ($record === null) {
            throw new Exception('cannot fetch record with uid ' . $uid, 1576572850);
        }
        if (!$this->tcaRegistry->isContainerElement($record['CType'])) {
            throw new Exception('not a container element with uid ' . $uid, 1576572851);
        }

        $defaultRecord = null;
        $language = (int)$record['sys_language_uid'];
        if ($language > 0) {
            $defaultRecord = $this->database->fetchOneDefaultRecord($record);
            if ($defaultRecord === null) {
                // free mode
                $childRecords = $this->database->fetchRecordsByParentAndLanguage($record['uid'], $language);
            } else {
                // connected mode
                $childRecords = $this->database->fetchRecordsByParentAndLanguage($defaultRecord['uid'], 0);
                $childRecords = $this->database->fetchOverlayRecords($childRecords, $language);
            }
        } else {
            $childRecords = $this->database->fetchRecordsByParentAndLanguage($record['uid'], $language);
        }
        $childRecordByColPosKey = $this->recordsByColPosKey($childRecords);
        if ($defaultRecord === null) {
            $container = new Container($record, $childRecordByColPosKey, $language);
        } else {
            $container = new Container($defaultRecord, $childRecordByColPosKey, $language);
        }
        return $container;
    }

    /**
     * @param array $records
     * @return array
     */
    protected function recordsByColPosKey(array $records): array
    {
        $recordsByColPosKey = [];
        foreach ($records as $record) {
            if (empty($recordsByColPosKey[$record['colPos']])) {
                $recordsByColPosKey[$record['colPos']] = [];
            }
            $recordsByColPosKey[$record['colPos']][] = $record;
        }
        return $recordsByColPosKey;
    }

}
