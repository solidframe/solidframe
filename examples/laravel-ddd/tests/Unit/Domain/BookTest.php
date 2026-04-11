<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Book\Book;
use App\Domain\Book\BookId;
use App\Domain\Book\BookStatus;
use App\Domain\Book\Exception\BookAlreadyBorrowedException;
use App\Domain\Book\Exception\BookNotBorrowedException;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    #[Test]
    public function addsNewBook(): void
    {
        $book = $this->createBook();

        self::assertSame('Clean Architecture', $book->title()->value());
        self::assertSame('Robert C. Martin', $book->author()->value());
        self::assertSame('9780134494166', $book->isbn()->value());
        self::assertSame(BookStatus::Available, $book->status());
        self::assertNull($book->borrower());
    }

    #[Test]
    public function borrowsAvailableBook(): void
    {
        $book = $this->createBook();

        $book->borrow('Kadir');

        self::assertSame(BookStatus::Borrowed, $book->status());
        self::assertSame('Kadir', $book->borrower());
    }

    #[Test]
    public function returnsABorrowedBook(): void
    {
        $book = $this->createBook();
        $book->borrow('Kadir');

        $book->return();

        self::assertSame(BookStatus::Available, $book->status());
        self::assertNull($book->borrower());
    }

    #[Test]
    public function cannotBorrowAlreadyBorrowedBook(): void
    {
        $book = $this->createBook();
        $book->borrow('Kadir');

        $this->expectException(BookAlreadyBorrowedException::class);

        $book->borrow('Ali');
    }

    #[Test]
    public function cannotReturnAvailableBook(): void
    {
        $book = $this->createBook();

        $this->expectException(BookNotBorrowedException::class);

        $book->return();
    }

    #[Test]
    public function updatesBookDetails(): void
    {
        $book = $this->createBook();

        $book->updateDetails(
            title: Title::from('Clean Code'),
            author: Author::from('Uncle Bob'),
            isbn: ISBN::from('9780132350884'),
        );

        self::assertSame('Clean Code', $book->title()->value());
        self::assertSame('Uncle Bob', $book->author()->value());
        self::assertSame('9780132350884', $book->isbn()->value());
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
