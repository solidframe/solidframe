<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Ddd;

use PHPStan\Rules\Rule;
use SolidFrame\PHPStanRules\Rules\Ddd\ValueObjectMustBeReadonlyRule;
use SolidFrame\PHPStanRules\Tests\Rules\SolidFrameRuleTestCase;

/** @extends SolidFrameRuleTestCase<ValueObjectMustBeReadonlyRule> */
final class ValueObjectMustBeReadonlyRuleTest extends SolidFrameRuleTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data';
    }

    protected function getRule(): Rule
    {
        return new ValueObjectMustBeReadonlyRule(
            valueObjectInterface: \SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures\TestValueObject::class,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/value-object.php'], [
            [
                'Value object "SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures\InvalidNonReadonlyValueObject" must be declared readonly.',
                14,
                'Value objects are immutable. Declare the class as readonly.',
            ],
        ]);
    }
}
