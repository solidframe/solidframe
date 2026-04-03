<?php

declare(strict_types=1);

namespace SolidFrame\Core\Tests\Identity;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Exception\InvalidArgumentException;
use SolidFrame\Core\Identity\UuidIdentity;

final class UuidIdentityTest extends TestCase
{
    #[Test]
    public function generatesValidUuid(): void
    {
        $id = UuidIdentity::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $id->value(),
        );
    }

    #[Test]
    public function acceptsValidUuid(): void
    {
        $id = new UuidIdentity('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $id->value());
    }

    #[Test]
    public function throwsOnInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UuidIdentity('not-a-uuid');
    }

    #[Test]
    public function generatesUniqueIds(): void
    {
        $id1 = UuidIdentity::generate();
        $id2 = UuidIdentity::generate();

        self::assertFalse($id1->equals($id2));
    }
}
