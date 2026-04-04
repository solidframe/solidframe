<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Tests\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Ddd\ValueObject\BoolValueObject;

final class BoolValueObjectTest extends TestCase
{
    #[Test]
    public function createsFromBool(): void
    {
        $true = ConcreteBool::from(true);
        $false = ConcreteBool::from(false);

        self::assertTrue($true->value());
        self::assertFalse($false->value());
    }

    #[Test]
    public function castsToString(): void
    {
        self::assertSame('true', (string) ConcreteBool::from(true));
        self::assertSame('false', (string) ConcreteBool::from(false));
    }

    #[Test]
    public function equalsSameTypeAndValue(): void
    {
        $a = ConcreteBool::from(true);
        $b = ConcreteBool::from(true);

        self::assertTrue($a->equals($b));
    }

    #[Test]
    public function doesNotEqualDifferentValue(): void
    {
        $a = ConcreteBool::from(true);
        $b = ConcreteBool::from(false);

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function doesNotEqualDifferentType(): void
    {
        $a = ConcreteBool::from(true);
        $b = AnotherBool::from(true);

        self::assertFalse($a->equals($b));
    }
}

final readonly class ConcreteBool extends BoolValueObject {}

final readonly class AnotherBool extends BoolValueObject {}
