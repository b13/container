<?php
namespace B13\Container\Tests\Unit\Hooks;


use B13\Container\Hooks\Datahandler;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DatahanderTest extends UnitTestCase
{
    /**
     * @test
     */
    public function extractContainerIdFromColPosModifiesCmdMap(): void
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
        $cmdmap = $dataHandlerHook->_call('extractContainerIdFromColPos', $cmdmap);
        $this->assertSame(34, $cmdmap['tt_content'][39]['copy']['update']['colPos']);
        $this->assertSame(2, $cmdmap['tt_content'][39]['copy']['update']['tx_container_parent']);
    }

    /**
     * @test
     */
    public function extractContainerIdFromColPosWithColPosIntegerReturnsOriginalCmdmap(): void
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
        $newCmdmap = $dataHandlerHook->_call('extractContainerIdFromColPos', $cmdmap);
        $this->assertSame($cmdmap, $newCmdmap);
    }
}
