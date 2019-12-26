<?php
namespace B13\Container\Tests\Unit\Hooks;


use B13\Container\Hooks\Datahandler;
use B13\Container\Hooks\DatahandlerDatabase;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DatahandlerTest extends UnitTestCase
{

    protected $resetSingletonInstances = true;


    /**
     * @test
     */
    public function datamapForLocalizationsExtendsDatamapWithLocalizations(): void
    {
        $database = $this->prophesize(DatahandlerDatabase::class);
        $defaultRecord = [
            'uid' => 2,
            'tx_container_parent' => 0,
            'sys_language_uid' => 0
        ];
        $database->fetchOverlayRecords($defaultRecord)->willReturn([['uid' => 3]]);
        $database->fetchOneRecord(2)->willReturn($defaultRecord);

        $dataHandlerHook = $this->getAccessibleMock(
            Datahandler::class,
            ['foo'],
            ['containerFactory' => null, 'datahandlerDatabase' => $database->reveal()]
        );
        $datamap = [
            'tt_content' => [
                2 => [
                    'colPos' => 200,
                    'tx_container_parent' => 1,
                    'sys_language_uid' => 0

                ]
            ]
        ];
        $modDatamap = $dataHandlerHook->_call('datamapForChildLocalizations', $datamap);
        $this->assertIsArray($modDatamap['tt_content'][3]);
        $this->assertSame(1, $modDatamap['tt_content'][3]['tx_container_parent']);
    }
    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToSplittedColPosValue(): void
    {
        $dataHandlerHook = $this->getAccessibleMock(Datahandler::class, ['foo']);
        $datamap = [
            'tt_content' => [
                39 => [
                    'colPos' => '2-34',
                    'sys_language_uid' => 0
                ]
            ]
        ];
        $datamap = $dataHandlerHook->_call('extractContainerIdFromColPosInDatamap', $datamap);
        $this->assertSame(34, $datamap['tt_content'][39]['colPos']);
        $this->assertSame(2, $datamap['tt_content'][39]['tx_container_parent']);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapSetsContainerIdToZeroValue(): void
    {
        $dataHandlerHook = $this->getAccessibleMock(Datahandler::class, ['foo']);
        $datamap = [
            'tt_content' => [
                39 => [
                    'colPos' => '0',
                    'sys_language_uid' => 0
                ]
            ]
        ];
        $datamap = $dataHandlerHook->_call('extractContainerIdFromColPosInDatamap', $datamap);
        $this->assertSame(0, $datamap['tt_content'][39]['colPos']);
        $this->assertSame(0, $datamap['tt_content'][39]['tx_container_parent']);
    }

}
