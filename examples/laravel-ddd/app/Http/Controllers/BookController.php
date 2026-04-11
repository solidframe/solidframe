<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Book\BookService;
use App\Domain\Book\Book;
use App\Http\Requests\BorrowBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final readonly class BookController
{
    public function __construct(private BookService $bookService) {}

    public function index(Request $request): JsonResponse
    {
        $books = $this->bookService->listBooks(
            author: $request->query('author'),
            onlyAvailable: $request->query('available') === 'true',
        );

        return new JsonResponse([
            'data' => array_map($this->toArray(...), $books),
        ]);
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->bookService->addBook(
            title: $request->validated('title'),
            author: $request->validated('author'),
            isbn: $request->validated('isbn'),
        );

        return new JsonResponse(['data' => $this->toArray($book)], Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    public function update(UpdateBookRequest $request, string $id): JsonResponse
    {
        $book = $this->bookService->updateBook(
            id: $id,
            title: $request->validated('title'),
            author: $request->validated('author'),
            isbn: $request->validated('isbn'),
        );

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->bookService->deleteBook($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function borrow(BorrowBookRequest $request, string $id): JsonResponse
    {
        $book = $this->bookService->borrowBook(
            id: $id,
            borrowerName: $request->validated('borrower'),
        );

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    public function return(string $id): JsonResponse
    {
        $book = $this->bookService->returnBook($id);

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    /** @return array<string, mixed> */
    private function toArray(Book $book): array
    {
        return [
            'id' => $book->identity()->value(),
            'title' => $book->title()->value(),
            'author' => $book->author()->value(),
            'isbn' => $book->isbn()->value(),
            'status' => $book->status()->value,
            'borrower' => $book->borrower(),
        ];
    }
}
