<?php

declare(strict_types=1);

namespace App\Domain\Book\Specification;

use App\Domain\Book\Book;
use App\Domain\Book\BookStatus;
use SolidFrame\Ddd\Specification\AbstractSpecification;

final class AvailableBookSpecification extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof Book
            && $candidate->status() === BookStatus::Available;
    }
}
