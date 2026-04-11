<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Book\Book;
use App\Domain\Book\BookId;
use App\Domain\Book\Specification\AvailableBookSpecification;
use App\Domain\Book\Specification\BookByAuthorSpecification;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SpecificationTest extends TestCase
{
    #[Test]
    public function availableBookSpecification(): void
    {
        $spec = new AvailableBookSpecification();
        $book = $this->createBook();

        self::assertTrue($spec->isSatisfiedBy($book));

        $book->borrow('Kadir');

        self::assertFalse($spec->isSatisfiedBy($book));
    }

    #[Test]
    public function bookByAuthorSpecification(): void
    {
        $spec = new BookByAuthorSpecification(Author::from('Robert C. Martin'));
        $book = $this->createBook();

        self::assertTrue($spec->isSatisfiedBy($book));

        $otherSpec = new BookByAuthorSpecification(Author::from('Martin Fowler'));

        self::assertFalse($otherSpec->isSatisfiedBy($book));
    }

    #[Test]
    public function composesSpecifications(): void
    {
        $available = new AvailableBookSpecification();
        $byAuthor = new BookByAuthorSpecification(Author::from('Robert C. Martin'));
        $composed = $available->and($byAuthor);

        $book = $this->createBook();

        self::assertTrue($composed->isSatisfiedBy($book));

        $book->borrow('Kadir');

        self::assertFalse($composed->isSatisfiedBy($book));
    }

    private function createBook(): Book
    {
        return Book::add(
            id: BookId::generate(),
            title: Title::from('Clean Architecture'),
            author: Author::from('Robert C. Martin'),
            isbn: ISBN::from('9780134494166'),
        );
    }
}
