<?php

return [
    'run' => [
        'handler' => 'Sys\Console\Run',
    ],
    'migrate'   => [
        'command' => ['-m', '--mgr'],
        'handler' => 'Sys\Migrations\Console',
        'methods' => [
            'help' => ['-h', '--help'],
            'create' => ['-c', '--cr'],
            'alter' => ['-a', '--alt'],
            'list' => ['-l', '--ls'],
        ],
    ],
    'create' => [
        'command' => ['-c', '--cr'],
        'handler' => 'Sys\Create\Console',
        'methods' => ['help' => ['-h', '--help']],
    ],
    'session'   => [
        'command' => ['-s', '--sess'],
        'handler' => 'Az\Session\Console',
        'methods' => ['help' => ['-h', '--help']],
    ],
    'log' => [
        'handler' => 'Sys\Exception\Console',
        'methods' => [
            'gc' => [], 'show' => [],
        ],
    ],
    // 'routes'    => 'Az\Route\Console',
    // 'deploy'    => 'Az\Deploy\Console',
];
