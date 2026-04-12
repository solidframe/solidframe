<?php

declare(strict_types=1);

namespace App\Application\Book;

use App\Domain\Book\Book;
use App\Domain\Book\BookId;
use App\Domain\Book\BookRepository;
use App\Domain\Book\Exception\BookNotFoundException;
use App\Domain\Book\Specification\AvailableBookSpecification;
use App\Domain\Book\Specification\BookByAuthorSpecification;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use SolidFrame\Core\Service\ApplicationServiceInterface;
use SolidFrame\Ddd\Specification\SpecificationInterface;

final readonly class BookService implements ApplicationServiceInterface
{
    public function __construct(private BookRepository $books) {}

    public function addBook(string $title, string $author, string $isbn): Book
    {
        $book = Book::add(
            id: BookId::generate(),
            title: Title::from($title),
            author: Author::from($author),
            isbn: ISBN::from($isbn),
        );

        $this->books->save($book);

        return $book;
    }

    public function getBook(string $id): Book
    {
        return $this->books->find(new BookId($id))
            ?? throw BookNotFoundException::forId($id);
    }

    /** @return list<Book> */
    public function listBooks(?string $author = null, bool $onlyAvailable = false): array
    {
        $spec = null;

        if ($author !== null && $author !== '') {
            $spec = new BookByAuthorSpecification(Author::from($author));
        }

        if ($onlyAvailable) {
            $available = new AvailableBookSpecification();
            $spec = $spec ? $spec->and($available) : $available;
        }

        return $spec ? $this->books->matching($spec) : $this->books->all();
    }

    public function updateBook(string $id, string $title, string $author, string $isbn): Book
    {
        $book = $this->getBook($id);

        $book->updateDetails(
            title: Title::from($title),
            author: Author::from($author),
            isbn: ISBN::from($isbn),
        );

        $this->books->save($book);

        return $book;
    }

    public function borrowBook(string $id, string $borrowerName): Book
    {
        $book = $this->getBook($id);
        $book->borrow($borrowerName);
        $this->books->save($book);

        return $book;
    }

    public function returnBook(string $id): Book
    {
        $book = $this->getBook($id);
        $book->return();
        $this->books->save($book);

        return $book;
    }

    public function deleteBook(string $id): void
    {
        $this->getBook($id);
        $this->books->delete(new BookId($id));
    }
}
