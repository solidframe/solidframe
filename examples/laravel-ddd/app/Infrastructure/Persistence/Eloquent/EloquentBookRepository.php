<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Book\Book;
use App\Domain\Book\BookId;
use App\Domain\Book\BookRepository;
use App\Domain\Book\BookStatus;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use SolidFrame\Ddd\Specification\SpecificationInterface;

final readonly class EloquentBookRepository implements BookRepository
{
    public function find(BookId $id): ?Book
    {
        $model = BookModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Book $book): void
    {
        BookModel::updateOrCreate(
            ['id' => $book->identity()->value()],
            [
                'title' => $book->title()->value(),
                'author' => $book->author()->value(),
                'isbn' => $book->isbn()->value(),
                'status' => $book->status()->value,
                'borrower' => $book->borrower(),
            ],
        );
    }

    public function delete(BookId $id): void
    {
        BookModel::where('id', $id->value())->delete();
    }

    /** @return list<Book> */
    public function all(): array
    {
        return BookModel::all()
            ->map(fn (BookModel $model) => $this->toDomain($model))
            ->values()
            ->all();
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

    private function toDomain(BookModel $model): Book
    {
        return Book::reconstitute(
            id: new BookId($model->id),
            title: Title::from($model->title),
            author: Author::from($model->author),
            isbn: ISBN::from($model->isbn),
            status: BookStatus::from($model->status),
            borrower: $model->borrower,
        );
    }
}
