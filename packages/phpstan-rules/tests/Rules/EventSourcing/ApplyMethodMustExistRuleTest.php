<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\EventSourcing;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\EventSourcing\ApplyMethodMustExistRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<ApplyMethodMustExistRule> */
final class ApplyMethodMustExistRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new ApplyMethodMustExistRule(
            aggregateRootClass: \SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures\TestAggregate::class,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/apply-method.php'], [
            [
                'Aggregate "SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures\InvalidAggregate" records "OrderCancelled" but is missing method "applyOrderCancelled()".',
                30,
            ],
        ]);
    }
}
