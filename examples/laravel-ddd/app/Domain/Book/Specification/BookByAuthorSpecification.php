<?php

declare(strict_types=1);

namespace App\Domain\Book\Specification;

use App\Domain\Book\Book;
use App\Domain\Book\ValueObject\Author;
use SolidFrame\Ddd\Specification\AbstractSpecification;

final class BookByAuthorSpecification extends AbstractSpecification
{
    public function __construct(private readonly Author $author) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Book
            && $candidate->author()->equals($this->author);
    }
}
