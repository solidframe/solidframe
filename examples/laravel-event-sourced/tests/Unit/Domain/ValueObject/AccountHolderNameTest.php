<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObject;

use App\Domain\Account\ValueObject\AccountHolderName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccountHolderNameTest extends TestCase
{
    #[Test]
    public function createsFromValidString(): void
    {
        $name = AccountHolderName::from('Kadir Posul');

        self::assertSame('Kadir Posul', $name->value);
    }

    #[Test]
    public function trimsWhitespace(): void
    {
        $name = AccountHolderName::from('  Kadir  ');

        self::assertSame('Kadir', $name->value);
    }

    #[Test]
    public function rejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AccountHolderName::from('');
    }

    #[Test]
    public function rejectsWhitespaceOnly(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AccountHolderName::from('   ');
    }

    #[Test]
    public function checksEquality(): void
    {
        $a = AccountHolderName::from('Kadir');
        $b = AccountHolderName::from('Kadir');
        $c = AccountHolderName::from('Ali');

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }
}
