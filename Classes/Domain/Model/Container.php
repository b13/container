<?php

namespace B13\Container\Domain\Model;

class Container
{
    /**
     * @var array
     */
    protected $containerRecord = null;

    /**
     * @var array
     */
    protected $childRecords = null;

    /**
     * @var int
     */
    protected $lanuage = 0;

    /**
     * @param array $containerRecord
     * @param array $childRecords
     * @param int $language
     */
    public function __construct(array $containerRecord, array $childRecords, $language = 0)
    {
        $this->containerRecord = $containerRecord;
        $this->childRecords = $childRecords;
        $this->language = $language;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return (int)$this->containerRecord['uid'];
    }

    /**
     * @return bool
     */
    public function isConnectedMode(): bool
    {
        return (int)$this->containerRecord['sys_language_uid'] === 0;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getCType(): string
    {
        return $this->containerRecord['CType'];
    }

    /**
     * @return array
     */
    public function getContainerRecord(): array
    {
        return $this->containerRecord;
    }

    /**
     * @return array
     */
    public function getChildRecords(): array
    {
        $childRecords = [];
        foreach ($this->childRecords as $colPos => $records) {
            $childRecords = array_merge($childRecords, $records);
        }
        return $childRecords;
    }

    /**
     * @param int $colPos
     * @return array
     */
    public function getChildsByColPos(int $colPos): array
    {
        if (empty($this->childRecords[$colPos])) {
            return [];
        }
        return $this->childRecords[$colPos];
    }

    /**
     * @return array
     */
    public function getChildsColPos(): array
    {
        return array_keys($this->childRecords);
    }

}
