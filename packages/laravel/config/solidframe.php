<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | Directories to scan for handlers, listeners, and sagas.
    | Paths are relative to the application base path.
    |
    */
    'discovery' => [
        'enabled' => true,
        'paths' => [
            'app',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CQRS
    |--------------------------------------------------------------------------
    |
    | Middleware classes to apply on command and query buses.
    |
    */
    'cqrs' => [
        'command_bus' => [
            'middleware' => [],
        ],
        'query_bus' => [
            'middleware' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event-Driven
    |--------------------------------------------------------------------------
    |
    | Middleware classes to apply on the event bus.
    |
    */
    'event_driven' => [
        'event_bus' => [
            'middleware' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Sourcing
    |--------------------------------------------------------------------------
    |
    | Configure the event store and snapshot store implementations.
    | Supported stores: "database"
    |
    */
    'event_sourcing' => [
        'event_store' => [
            'driver' => 'database',
            'connection' => null, // null = default connection
            'table' => 'event_store',
        ],
        'snapshot_store' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'snapshots',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Saga
    |--------------------------------------------------------------------------
    |
    | Configure the saga store implementation.
    | Supported drivers: "database", "redis"
    |
    */
    'saga' => [
        'store' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'sagas',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modular
    |--------------------------------------------------------------------------
    |
    | Configure module discovery and auto-registration.
    |
    */
    'modular' => [
        'path' => 'modules',
        'auto_discovery' => true,
    ],
];
