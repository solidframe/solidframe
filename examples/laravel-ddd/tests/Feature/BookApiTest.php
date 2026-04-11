<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_ISBN = '9780134494166';
    private const ANOTHER_ISBN = '9780132350884';

    #[Test]
    public function addsABook(): void
    {
        $response = $this->postJson('/api/books', [
            'title' => 'Clean Architecture',
            'author' => 'Robert C. Martin',
            'isbn' => self::VALID_ISBN,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Clean Architecture')
            ->assertJsonPath('data.author', 'Robert C. Martin')
            ->assertJsonPath('data.isbn', self::VALID_ISBN)
            ->assertJsonPath('data.status', 'available')
            ->assertJsonPath('data.borrower', null);
    }

    #[Test]
    public function validatesRequiredFields(): void
    {
        $response = $this->postJson('/api/books', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'author', 'isbn']);
    }

    #[Test]
    public function rejectsInvalidISBN(): void
    {
        $response = $this->postJson('/api/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => 'invalid',
        ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function listsAllBooks(): void
    {
        $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->addBook('Clean Code', 'Robert C. Martin', self::ANOTHER_ISBN);

        $response = $this->getJson('/api/books');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function filtersBooksByAuthor(): void
    {
        $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->addBook('Refactoring', 'Martin Fowler', '9780134757599');

        $response = $this->getJson('/api/books?author=Robert+C.+Martin');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.author', 'Robert C. Martin');
    }

    #[Test]
    public function filtersAvailableBooks(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->addBook('Clean Code', 'Robert C. Martin', self::ANOTHER_ISBN);

        $this->postJson("/api/books/{$book['id']}/borrow", ['borrower' => 'Kadir']);

        $response = $this->getJson('/api/books?available=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function showsBookDetails(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $response = $this->getJson("/api/books/{$book['id']}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Clean Architecture');
    }

    #[Test]
    public function returns404ForNonExistentBook(): void
    {
        $response = $this->getJson('/api/books/00000000-0000-4000-8000-000000000000');

        $response->assertNotFound();
    }

    #[Test]
    public function updatesBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $response = $this->putJson("/api/books/{$book['id']}", [
            'title' => 'Clean Code',
            'author' => 'Uncle Bob',
            'isbn' => self::ANOTHER_ISBN,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Clean Code')
            ->assertJsonPath('data.author', 'Uncle Bob');
    }

    #[Test]
    public function deletesBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->deleteJson("/api/books/{$book['id']}")->assertNoContent();

        $this->getJson("/api/books/{$book['id']}")->assertNotFound();
    }

    #[Test]
    public function borrowsABook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $response = $this->postJson("/api/books/{$book['id']}/borrow", [
            'borrower' => 'Kadir',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'borrowed')
            ->assertJsonPath('data.borrower', 'Kadir');
    }

    #[Test]
    public function cannotBorrowAlreadyBorrowedBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->postJson("/api/books/{$book['id']}/borrow", ['borrower' => 'Kadir']);

        $response = $this->postJson("/api/books/{$book['id']}/borrow", [
            'borrower' => 'Ali',
        ]);

        $response->assertConflict();
    }

    #[Test]
    public function returnsABorrowedBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->postJson("/api/books/{$book['id']}/borrow", ['borrower' => 'Kadir']);

        $response = $this->postJson("/api/books/{$book['id']}/return");

        $response->assertOk()
            ->assertJsonPath('data.status', 'available')
            ->assertJsonPath('data.borrower', null);
    }

    #[Test]
    public function cannotReturnAvailableBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $response = $this->postJson("/api/books/{$book['id']}/return");

        $response->assertConflict();
    }

    /** @return array<string, mixed> */
    private function addBook(string $title, string $author, string $isbn): array
    {
        $response = $this->postJson('/api/books', [
            'title' => $title,
            'author' => $author,
            'isbn' => $isbn,
        ]);

        return $response->json('data');
    }
}
