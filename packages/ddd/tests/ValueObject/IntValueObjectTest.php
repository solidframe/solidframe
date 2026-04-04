<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Tests\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Ddd\ValueObject\IntValueObject;

final class IntValueObjectTest extends TestCase
{
    #[Test]
    public function createsFromInt(): void
    {
        $vo = ConcreteInt::from(42);

        self::assertSame(42, $vo->value());
    }

    #[Test]
    public function castsToString(): void
    {
        $vo = ConcreteInt::from(42);

        self::assertSame('42', (string) $vo);
    }

    #[Test]
    public function equalsSameTypeAndValue(): void
    {
        $a = ConcreteInt::from(42);
        $b = ConcreteInt::from(42);

        self::assertTrue($a->equals($b));
    }

    #[Test]
    public function doesNotEqualDifferentValue(): void
    {
        $a = ConcreteInt::from(42);
        $b = ConcreteInt::from(99);

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function doesNotEqualDifferentType(): void
    {
        $a = ConcreteInt::from(42);
        $b = AnotherInt::from(42);

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function handlesZero(): void
    {
        $vo = ConcreteInt::from(0);

        self::assertSame(0, $vo->value());
        self::assertSame('0', (string) $vo);
    }

    #[Test]
    public function handlesNegative(): void
    {
        $vo = ConcreteInt::from(-5);

        self::assertSame(-5, $vo->value());
    }
}

final readonly class ConcreteInt extends IntValueObject {}

final readonly class AnotherInt extends IntValueObject {}
