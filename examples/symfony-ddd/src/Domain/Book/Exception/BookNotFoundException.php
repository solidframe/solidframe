<?php

declare(strict_types=1);

namespace App\Domain\Book\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

final class BookNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Book with id "%s" was not found.', $id));
    }
}
