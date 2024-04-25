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

use B13\Container\Tests\Functional\Datahandler\AbstractDatahandler;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyContainerTest extends AbstractDatahandler
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example',
        'typo3conf/ext/content_defender',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() > 11) {
            // content_defender calls FormDataCompiler which wants access global variable TYPO3_REQUEST
            $GLOBALS['TYPO3_REQUEST'] = null;
        }
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
