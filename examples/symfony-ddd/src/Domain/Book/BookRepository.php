<?php

declare(strict_types=1);

namespace App\Domain\Book;

use SolidFrame\Ddd\Specification\SpecificationInterface;

interface BookRepository
{
    public function find(BookId $id): ?Book;

    public function save(Book $book): void;

    public function delete(BookId $id): void;

    /** @return list<Book> */
    public function all(): array;

    /** @return list<Book> */
    public function matching(SpecificationInterface $specification): array;
}
