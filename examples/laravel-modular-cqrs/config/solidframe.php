<?php

declare(strict_types=1);

return [
    'discovery' => [
        'enabled' => true,
        'paths' => ['modules'],
    ],
    'modular' => [
        'path' => 'modules',
        'auto_discovery' => false,
    ],
    'saga' => [
        'store' => [
            'driver' => 'memory',
            'connection' => null,
            'table' => 'sagas',
        ],
    ],
];
