<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/packages/*/src',
        __DIR__ . '/packages/*/tests',
    ])
    ->withSkip([
        __DIR__ . '/packages/phpstan-rules/tests/Rules/Cqrs/data/*',
        __DIR__ . '/packages/phpstan-rules/tests/Rules/Ddd/data/*',
        __DIR__ . '/packages/phpstan-rules/tests/Rules/EventSourcing/data/*',
        RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__ . '/packages/laravel/tests/Discovery/Fixtures/*',
            __DIR__ . '/packages/laravel/tests/Cqrs/ContainerHandlerResolverTest.php',
            __DIR__ . '/packages/laravel/tests/EventDriven/ContainerListenerResolverTest.php',
            __DIR__ . '/packages/symfony/tests/Discovery/Fixtures/*',
        ],
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    );
