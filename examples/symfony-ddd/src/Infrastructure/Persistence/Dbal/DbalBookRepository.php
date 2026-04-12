<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dbal;

use App\Domain\Book\Book;
use App\Domain\Book\BookId;
use App\Domain\Book\BookRepository;
use App\Domain\Book\BookStatus;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use Doctrine\DBAL\Connection;
use SolidFrame\Ddd\Specification\SpecificationInterface;

final readonly class DbalBookRepository implements BookRepository
{
    public function __construct(private Connection $connection) {}

    public function find(BookId $id): ?Book
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM books WHERE id = ?',
            [$id->value()],
        );

        return $row !== false ? $this->toDomain($row) : null;
    }

    public function save(Book $book): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM books WHERE id = ?',
            [$book->identity()->value()],
        );

        if ($exists !== false) {
            $this->connection->update('books', [
                'title' => $book->title()->value(),
                'author' => $book->author()->value(),
                'isbn' => $book->isbn()->value(),
                'status' => $book->status()->value,
                'borrower' => $book->borrower(),
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], ['id' => $book->identity()->value()]);
        } else {
            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $this->connection->insert('books', [
                'id' => $book->identity()->value(),
                'title' => $book->title()->value(),
                'author' => $book->author()->value(),
                'isbn' => $book->isbn()->value(),
                'status' => $book->status()->value,
                'borrower' => $book->borrower(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function delete(BookId $id): void
    {
        $this->connection->delete('books', ['id' => $id->value()]);
    }

    /** @return list<Book> */
    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM books');

        return array_map($this->toDomain(...), $rows);
    }

    /** @return list<Book> */
    public function matching(SpecificationInterface $specification): array
    {
        return array_values(
            array_filter(
                $this->all(),
                fn (Book $book) => $specification->isSatisfiedBy($book),
            ),
        );
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Book
    {
        return Book::reconstitute(
            id: new BookId($row['id']),
            title: Title::from($row['title']),
            author: Author::from($row['author']),
            isbn: ISBN::from($row['isbn']),
            status: BookStatus::from($row['status']),
            borrower: $row['borrower'],
        );
    }
}
