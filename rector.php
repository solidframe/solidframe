<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/packages/*/src',
        __DIR__ . '/packages/*/tests',
    ])
    ->withSkip([
        __DIR__ . '/packages/phpstan-rules/tests/Rules/Cqrs/data/*',
        __DIR__ . '/packages/phpstan-rules/tests/Rules/Ddd/data/*',
        __DIR__ . '/packages/phpstan-rules/tests/Rules/EventSourcing/data/*',
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    );
