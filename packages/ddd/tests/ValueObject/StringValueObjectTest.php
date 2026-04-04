<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Tests\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final class StringValueObjectTest extends TestCase
{
    #[Test]
    public function createsFromString(): void
    {
        $vo = ConcreteString::from('hello');

        self::assertSame('hello', $vo->value());
    }

    #[Test]
    public function castsToString(): void
    {
        $vo = ConcreteString::from('hello');

        self::assertSame('hello', (string) $vo);
    }

    #[Test]
    public function equalsSameTypeAndValue(): void
    {
        $a = ConcreteString::from('hello');
        $b = ConcreteString::from('hello');

        self::assertTrue($a->equals($b));
    }

    #[Test]
    public function doesNotEqualDifferentValue(): void
    {
        $a = ConcreteString::from('hello');
        $b = ConcreteString::from('world');

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function doesNotEqualDifferentType(): void
    {
        $a = ConcreteString::from('hello');
        $b = AnotherString::from('hello');

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function allowsEmptyByDefault(): void
    {
        $vo = ConcreteString::from('');

        self::assertSame('', $vo->value());
    }
}

final readonly class ConcreteString extends StringValueObject {}

final readonly class AnotherString extends StringValueObject {}
