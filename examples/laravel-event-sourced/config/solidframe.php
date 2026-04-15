<?php

declare(strict_types=1);

return [
    'event_sourcing' => [
        'event_store' => [
            'driver' => 'database',
            'table' => 'event_store',
        ],
        'snapshot_store' => [
            'driver' => 'database',
            'table' => 'snapshot_store',
        ],
    ],
];
