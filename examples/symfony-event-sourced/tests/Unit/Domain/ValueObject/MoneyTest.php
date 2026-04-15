<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Account\Currency;
use App\Domain\Account\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    #[Test]
    public function addsSameCurrency(): void
    {
        $a = Money::of(1000, Currency::TRY);
        $b = Money::of(500, Currency::TRY);

        $result = $a->add($b);

        self::assertSame(1500, $result->amount);
        self::assertSame(Currency::TRY, $result->currency);
    }

    #[Test]
    public function subtractsSameCurrency(): void
    {
        $a = Money::of(1000, Currency::TRY);
        $b = Money::of(300, Currency::TRY);

        $result = $a->subtract($b);

        self::assertSame(700, $result->amount);
    }

    #[Test]
    public function rejectsDifferentCurrencyAdd(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::of(100, Currency::TRY)->add(Money::of(100, Currency::USD));
    }

    #[Test]
    public function rejectsDifferentCurrencySubtract(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::of(100, Currency::TRY)->subtract(Money::of(100, Currency::EUR));
    }

    #[Test]
    public function comparesGreaterThanOrEqual(): void
    {
        $a = Money::of(1000, Currency::TRY);
        $b = Money::of(500, Currency::TRY);
        $c = Money::of(1000, Currency::TRY);

        self::assertTrue($a->isGreaterThanOrEqual($b));
        self::assertTrue($a->isGreaterThanOrEqual($c));
        self::assertFalse($b->isGreaterThanOrEqual($a));
    }

    #[Test]
    public function detectsNegative(): void
    {
        self::assertTrue(Money::of(-1, Currency::TRY)->isNegative());
        self::assertFalse(Money::of(0, Currency::TRY)->isNegative());
        self::assertFalse(Money::of(1, Currency::TRY)->isNegative());
    }

    #[Test]
    public function checksEquality(): void
    {
        $a = Money::of(1000, Currency::TRY);
        $b = Money::of(1000, Currency::TRY);
        $c = Money::of(1000, Currency::USD);

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    #[Test]
    public function createsZero(): void
    {
        $zero = Money::zero(Currency::EUR);

        self::assertSame(0, $zero->amount);
        self::assertSame(Currency::EUR, $zero->currency);
    }
}
