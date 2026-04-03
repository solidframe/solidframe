<?php

declare(strict_types=1);

namespace SolidFrame\Core\Tests\Identity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Exception\InvalidArgumentException;
use SolidFrame\Core\Identity\AbstractIdentity;

final class AbstractIdentityTest extends TestCase
{
    #[Test]
    public function storesAndReturnsValue(): void
    {
        $id = $this->createIdentity('abc-123');

        self::assertSame('abc-123', $id->value());
    }

    #[Test]
    public function castsToString(): void
    {
        $id = $this->createIdentity('abc-123');

        self::assertSame('abc-123', (string) $id);
    }

    #[Test]
    public function equalsSameTypeAndValue(): void
    {
        $id1 = $this->createIdentity('abc-123');
        $id2 = $this->createIdentity('abc-123');

        self::assertTrue($id1->equals($id2));
    }

    #[Test]
    public function doesNotEqualDifferentValue(): void
    {
        $id1 = $this->createIdentity('abc-123');
        $id2 = $this->createIdentity('xyz-789');

        self::assertFalse($id1->equals($id2));
    }

    #[Test]
    public function throwsOnEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createIdentity('');
    }

    private function createIdentity(string $value): AbstractIdentity
    {
        return new class ($value) extends AbstractIdentity {};
    }
}
