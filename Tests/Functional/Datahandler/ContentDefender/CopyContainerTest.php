<?php

declare(strict_types=1);

namespace B13\Container\Tests\Functional\Datahandler\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Information\Typo3Version;

class CopyContainerTest extends AbstractContentDefender
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
    ];

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14) {
            $this->testExtensionsToLoad[] = 'typo3conf/ext/content_defender';
        }
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/copy_container.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyContainerAfterElementCopiesChildEvenChildIsNotAllowedByContentDefenderInBackendLayout(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -3,
                        'update' => [
                            'colPos' => 9,
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyContainerAfterElementCopiesChildEvenChildIsNotAllowedByContentDefenderInBackendLayoutResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyContainerIntoOtherContainerWithSameColPosCopiesAlsoChildEvenChildIsDisallowedInTargetContainer(): void
    {
        $cmdmap = [
            'tt_content' => [
                4 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => -2,
                        'update' => [
                            'colPos' => '1-200',
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyContainerIntoOtherContainerWithSameColPosCopiesAlsoChildEvenChildIsDisallowedInTargetContainerResult.csv');
    }

    /**
     * @test
     * @group content_defender
     */
    public function copyContainerWithRestrictionsIgnoresContentDefender(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/copy_container_with_restrictions.csv');
        $cmdmap = [
            'tt_content' => [
                11 => [
                    'copy' => [
                        'action' => 'paste',
                        'target' => 2,
                        'update' => [
                            'colPos' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        self::assertCSVDataSet(__DIR__ . '/Fixtures/CopyContainerWithRestrictionsIgnoresContentDefenderResult.csv');
    }
}
