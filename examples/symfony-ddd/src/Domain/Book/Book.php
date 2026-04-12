<?php

declare(strict_types=1);

namespace App\Domain\Book;

use App\Domain\Book\Exception\BookAlreadyBorrowedException;
use App\Domain\Book\Exception\BookNotBorrowedException;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Book extends AbstractAggregateRoot
{
    private Title $title;
    private Author $author;
    private ISBN $isbn;
    private BookStatus $status;
    private ?string $borrower;

    private function __construct(
        BookId $id,
        Title $title,
        Author $author,
        ISBN $isbn,
    ) {
        parent::__construct($id);
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->status = BookStatus::Available;
        $this->borrower = null;
    }

    public static function add(BookId $id, Title $title, Author $author, ISBN $isbn): self
    {
        return new self($id, $title, $author, $isbn);
    }

    public static function reconstitute(
        BookId $id,
        Title $title,
        Author $author,
        ISBN $isbn,
        BookStatus $status,
        ?string $borrower,
    ): self {
        $book = new self($id, $title, $author, $isbn);
        $book->status = $status;
        $book->borrower = $borrower;

        return $book;
    }

    public function updateDetails(Title $title, Author $author, ISBN $isbn): void
    {
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
    }

    public function borrow(string $borrowerName): void
    {
        ($this->status === BookStatus::Available)
            or throw BookAlreadyBorrowedException::forId($this->identity()->value());

        $this->status = BookStatus::Borrowed;
        $this->borrower = $borrowerName;
    }

    public function return(): void
    {
        ($this->status === BookStatus::Borrowed)
            or throw BookNotBorrowedException::forId($this->identity()->value());

        $this->status = BookStatus::Available;
        $this->borrower = null;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function author(): Author
    {
        return $this->author;
    }

    public function isbn(): ISBN
    {
        return $this->isbn;
    }

    public function status(): BookStatus
    {
        return $this->status;
    }

    public function borrower(): ?string
    {
        return $this->borrower;
    }
}
