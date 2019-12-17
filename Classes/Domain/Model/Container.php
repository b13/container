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
     * Container constructor.
     * @param array $containerRecord
     * @param array $childRecords
     */
    public function __construct(array $containerRecord, array $childRecords)
    {
        $this->containerRecord = $containerRecord;
        $this->childRecords = $childRecords;
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
