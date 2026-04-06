<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Ddd;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Ddd\NoDirectAggregateConstructionRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<NoDirectAggregateConstructionRule> */
final class NoDirectAggregateConstructionRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new NoDirectAggregateConstructionRule(
            aggregateRootClass: \SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures\TestAggregateRoot::class,
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/aggregate-construction.php'], [
            [
                'Aggregate root "SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures\Order" must not be constructed directly. Use a named constructor or factory method.',
                24,
                'Use a static factory method like MyAggregate::create() instead of new MyAggregate().',
            ],
        ]);
    }
}
