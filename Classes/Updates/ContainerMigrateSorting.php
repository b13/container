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
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class ContainerMigrateSorting implements UpgradeWizardInterface, RepeatableInterface
{
    public const IDENTIFIER = 'container_migratesorting';

    /**
     * @var Sorting
     */
    protected $sorting;

    public function __construct(Sorting $sorting)
    {
        $this->sorting = $sorting;
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    /**
     * @return string Title of this updater
     */
    public function getTitle(): string
    {
        return 'EXT:container: Migrate "container" sorting';
    }

    /**
     * @return string Longer description of this updater
     */
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
            Bootstrap::initializeBackendUser();
            Bootstrap::initializeBackendAuthentication();
            Bootstrap::initializeLanguageObject();
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
