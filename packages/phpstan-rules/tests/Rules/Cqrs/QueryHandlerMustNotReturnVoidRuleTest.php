<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Cqrs\QueryHandlerMustNotReturnVoidRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<QueryHandlerMustNotReturnVoidRule> */
final class QueryHandlerMustNotReturnVoidRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new QueryHandlerMustNotReturnVoidRule(
            handlerInterface: \SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\TestQueryHandler::class,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/query-handler.php'], [
            [
                'Query handler "SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures\InvalidQueryHandler::__invoke" must not return void.',
                19,
                'Query handlers must return data. Use a command handler for side effects.',
            ],
        ]);
    }
}
