<?php
declare(strict_types = 1);
namespace B13\Container\Tests\Acceptance\Support;


use B13\Container\Tests\Acceptance\Support\_generated\BackendTesterActions;
use TYPO3\TestingFramework\Core\Acceptance\Step\FrameSteps;

class BackendTester extends \Codeception\Actor
{
    use BackendTesterActions;
    use FrameSteps;
}
