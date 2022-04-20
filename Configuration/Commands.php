<?php

return [
    'container:integrity' => [
        'class' => \B13\Container\Command\IntegrityCommand::class,
    ],
    'container:deleteChildrenWithWrongPid' => [
        'class' => \B13\Container\Command\DeleteChildrenWithWrongPidCommand::class,
    ],
    'container:fixContainerParentForConnectedModeCommand' => [
        'class' => \B13\Container\Command\FixContainerParentForConnectedModeCommand::class,
    ],
    'container:fixLanguageModeCommand' => [
        'class' => \B13\Container\Command\FixLanguageModeCommand::class,
    ],
    'integrity:run' => [
        'class' => \B13\Container\Command\IntegrityCommand::class,
    ],
];
