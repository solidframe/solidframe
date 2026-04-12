<?php

declare(strict_types=1);

namespace App\Domain\Book\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

final class BookNotBorrowedException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Book "%s" is not currently borrowed.', $id));
    }
}
