<?php

declare(strict_types=1);

namespace SolidFrame\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Exception\EntityNotFoundException;
use SolidFrame\Core\Exception\InvalidArgumentException;
use SolidFrame\Core\Exception\SolidFrameException;

final class ExceptionTest extends TestCase
{
    #[Test]
    public function invalidArgumentExceptionImplementsMarker(): void
    {
        $exception = InvalidArgumentException::emptyIdentity('UserId');

        self::assertInstanceOf(SolidFrameException::class, $exception);
        self::assertStringContainsString('UserId', $exception->getMessage());
    }

    #[Test]
    public function invalidUuidHasDescriptiveMessage(): void
    {
        $exception = InvalidArgumentException::invalidUuid('not-valid');

        self::assertStringContainsString('not-valid', $exception->getMessage());
    }

    #[Test]
    public function entityNotFoundForId(): void
    {
        $exception = EntityNotFoundException::forId('abc-123');

        self::assertInstanceOf(SolidFrameException::class, $exception);
        self::assertStringContainsString('abc-123', $exception->getMessage());
    }

    #[Test]
    public function entityNotFoundForClassAndId(): void
    {
        $exception = EntityNotFoundException::forClassAndId('Order', 'abc-123');

        self::assertStringContainsString('Order', $exception->getMessage());
        self::assertStringContainsString('abc-123', $exception->getMessage());
    }
}
