<?php

declare(strict_types=1);

namespace B13\Container\Updates;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Integrity\Sorting;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('container_containerMigrateSorting')]
#[Autoconfigure(public: true)]
class ContainerMigrateSorting implements UpgradeWizardInterface, RepeatableInterface, ChattyInterface
{
    public const IDENTIFIER = 'container_migratesorting';

    private OutputInterface $output;

    public function __construct(protected Sorting $sorting)
    {
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function getTitle(): string
    {
        return 'EXT:container: Migrate "container" sorting';
    }

    public function getDescription(): string
    {
        return 'change sorting of container children (must be run multiple times for nested containers)';
    }

    public function updateNecessary(): bool
    {
        $errors = $this->sorting->run(true);
        return !empty($errors);
    }

    public function executeUpdate(): bool
    {
        if (Environment::isCli() === false) {
            $requestFactory = GeneralUtility::makeInstance(ServerRequestFactory::class);
            $request = $requestFactory::fromGlobals();
            $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
            Bootstrap::initializeBackendUser(BackendUserAuthentication::class, $request);
            if ($GLOBALS['BE_USER'] === null || $GLOBALS['BE_USER']->user === null) {
                $this->output->writeln(
                    '<error>EXT:container Migrations need a valid Backend User, Login to the Backend to execute Wizard, or use CLI</error>'
                );
                return false;
            }
            Bootstrap::initializeBackendAuthentication();
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
        }
        $this->sorting->run(false);
        return true;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
