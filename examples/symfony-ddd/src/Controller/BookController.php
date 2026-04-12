<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Book\BookService;
use App\Domain\Book\Book;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/books')]
final readonly class BookController
{
    public function __construct(
        private BookService $bookService,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $books = $this->bookService->listBooks(
            author: $request->query->getString('author') ?: null,
            onlyAvailable: $request->query->getString('available') === 'true',
        );

        return new JsonResponse([
            'data' => array_map($this->toArray(...), $books),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request, new Assert\Collection([
            'title' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'author' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'isbn' => [new Assert\NotBlank()],
        ]));

        $book = $this->bookService->addBook(
            title: $data['title'],
            author: $data['author'],
            isbn: $data['isbn'],
        );

        return new JsonResponse(['data' => $this->toArray($book)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->validateRequest($request, new Assert\Collection([
            'title' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'author' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'isbn' => [new Assert\NotBlank()],
        ]));

        $book = $this->bookService->updateBook(
            id: $id,
            title: $data['title'],
            author: $data['author'],
            isbn: $data['isbn'],
        );

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function destroy(string $id): JsonResponse
    {
        $this->bookService->deleteBook($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/borrow', methods: ['POST'])]
    public function borrow(Request $request, string $id): JsonResponse
    {
        $data = $this->validateRequest($request, new Assert\Collection([
            'borrower' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
        ]));

        $book = $this->bookService->borrowBook(
            id: $id,
            borrowerName: $data['borrower'],
        );

        return new JsonResponse(['data' => $this->toArray($book)]);
    }

    #[Route('/{id}/return', methods: ['POST'])]
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

    /**
     * @return array<string, mixed>
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    private function validateRequest(Request $request, Assert\Collection $constraints): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($request->getContent(), true) ?? [];

        $violations = $this->validator->validate($data, $constraints);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $field = trim($violation->getPropertyPath(), '[]');
                $errors[$field][] = $violation->getMessage();
            }

            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                json_encode(['errors' => $errors], JSON_THROW_ON_ERROR),
            );
        }

        return $data;
    }
}
