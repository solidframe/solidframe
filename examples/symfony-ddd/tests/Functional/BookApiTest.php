<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BookApiTest extends WebTestCase
{
    private const VALID_ISBN = '9780134494166';
    private const ANOTHER_ISBN = '9780132350884';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        // Drop and recreate for clean state
        $tables = $connection->createSchemaManager()->listTableNames();
        if (in_array('books', $tables, true)) {
            $connection->executeStatement('DELETE FROM books');
        } else {
            (new SchemaManager($connection))->createSchema();
        }
    }

    #[Test]
    public function addsABook(): void
    {
        $this->client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Clean Architecture',
            'author' => 'Robert C. Martin',
            'isbn' => self::VALID_ISBN,
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('Clean Architecture', $data['title']);
        self::assertSame('Robert C. Martin', $data['author']);
        self::assertSame(self::VALID_ISBN, $data['isbn']);
        self::assertSame('available', $data['status']);
        self::assertNull($data['borrower']);
    }

    #[Test]
    public function validatesRequiredFields(): void
    {
        $this->client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function rejectsInvalidISBN(): void
    {
        $this->client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => 'invalid',
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function listsAllBooks(): void
    {
        $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->addBook('Clean Code', 'Robert C. Martin', self::ANOTHER_ISBN);

        $this->client->request('GET', '/api/books');

        self::assertResponseIsSuccessful();

        $json = $this->json();
        self::assertCount(2, $json['data']);
    }

    #[Test]
    public function filtersBooksByAuthor(): void
    {
        $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->addBook('Refactoring', 'Martin Fowler', '9780134757599');

        $this->client->request('GET', '/api/books?author=Robert+C.+Martin');

        self::assertResponseIsSuccessful();

        $json = $this->json();
        self::assertCount(1, $json['data']);
        self::assertSame('Robert C. Martin', $json['data'][0]['author']);
    }

    #[Test]
    public function filtersAvailableBooks(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->addBook('Clean Code', 'Robert C. Martin', self::ANOTHER_ISBN);

        $this->client->request('POST', "/api/books/{$book['id']}/borrow", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['borrower' => 'Kadir']));

        $this->client->request('GET', '/api/books?available=true');

        self::assertResponseIsSuccessful();

        $json = $this->json();
        self::assertCount(1, $json['data']);
    }

    #[Test]
    public function showsBookDetails(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->client->request('GET', "/api/books/{$book['id']}");

        self::assertResponseIsSuccessful();
        self::assertSame('Clean Architecture', $this->responseData()['title']);
    }

    #[Test]
    public function returns404ForNonExistentBook(): void
    {
        $this->client->request('GET', '/api/books/00000000-0000-4000-8000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function updatesBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->client->request('PUT', "/api/books/{$book['id']}", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Clean Code',
            'author' => 'Uncle Bob',
            'isbn' => self::ANOTHER_ISBN,
        ]));

        self::assertResponseIsSuccessful();

        $data = $this->responseData();
        self::assertSame('Clean Code', $data['title']);
        self::assertSame('Uncle Bob', $data['author']);
    }

    #[Test]
    public function deletesBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->client->request('DELETE', "/api/books/{$book['id']}");
        self::assertResponseStatusCodeSame(204);

        $this->client->request('GET', "/api/books/{$book['id']}");
        self::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function borrowsABook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->client->request('POST', "/api/books/{$book['id']}/borrow", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'borrower' => 'Kadir',
        ]));

        self::assertResponseIsSuccessful();

        $data = $this->responseData();
        self::assertSame('borrowed', $data['status']);
        self::assertSame('Kadir', $data['borrower']);
    }

    #[Test]
    public function cannotBorrowAlreadyBorrowedBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->client->request('POST', "/api/books/{$book['id']}/borrow", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['borrower' => 'Kadir']));

        $this->client->request('POST', "/api/books/{$book['id']}/borrow", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['borrower' => 'Ali']));

        self::assertResponseStatusCodeSame(409);
    }

    #[Test]
    public function returnsABorrowedBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);
        $this->client->request('POST', "/api/books/{$book['id']}/borrow", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['borrower' => 'Kadir']));

        $this->client->request('POST', "/api/books/{$book['id']}/return");

        self::assertResponseIsSuccessful();

        $data = $this->responseData();
        self::assertSame('available', $data['status']);
        self::assertNull($data['borrower']);
    }

    #[Test]
    public function cannotReturnAvailableBook(): void
    {
        $book = $this->addBook('Clean Architecture', 'Robert C. Martin', self::VALID_ISBN);

        $this->client->request('POST', "/api/books/{$book['id']}/return");

        self::assertResponseStatusCodeSame(409);
    }

    /** @return array<string, mixed> */
    private function addBook(string $title, string $author, string $isbn): array
    {
        $this->client->request('POST', '/api/books', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => $title,
            'author' => $author,
            'isbn' => $isbn,
        ]));

        return $this->responseData();
    }

    /** @return array<string, mixed> */
    private function json(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    /** @return array<string, mixed> */
    private function responseData(): array
    {
        return $this->json()['data'];
    }
}
