<?php
namespace B13\Container\Tests\Unit\Hooks;


use B13\Container\Hooks\Datahandler;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DatahandlerTest extends UnitTestCase
{

    protected $resetSingletonInstances = true;
    /**
     * @test
     */
    public function extractContainerIdFromColPosOnUpdateModifiesCmdMap(): void
    {
        $dataHandlerHook = $this->getAccessibleMock(Datahandler::class, ['foo']);
        $cmdmap = [
            'tt_content' => [
                39 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 137, // pageId
                        'update' => [
                            'colPos' => '2-34',
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $cmdmap = $dataHandlerHook->_call('extractContainerIdFromColPosOnUpdate', $cmdmap);
        $this->assertSame(34, $cmdmap['tt_content'][39]['copy']['update']['colPos']);
        $this->assertSame(2, $cmdmap['tt_content'][39]['copy']['update']['tx_container_parent']);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosOnUpdateWithColPosIntegerReturnsOriginalCmdmap(): void
    {
        $dataHandlerHook = $this->getAccessibleMock(Datahandler::class, ['foo']);
        $cmdmap = [
            'tt_content' => [
                39 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 137, // pageId
                        'update' => [
                            'colPos' => 34,
                            'sys_language_uid' => 0

                        ]
                    ]
                ]
            ]
        ];
        $newCmdmap = $dataHandlerHook->_call('extractContainerIdFromColPosOnUpdate', $cmdmap);
        $this->assertSame(34, $newCmdmap['tt_content'][39]['copy']['update']['colPos']);
        $this->assertSame(0, $newCmdmap['tt_content'][39]['copy']['update']['tx_container_parent']);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosInDatamapModifiesDatamap(): void
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
}
