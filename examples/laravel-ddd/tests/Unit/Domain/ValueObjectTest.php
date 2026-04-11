<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Book\Exception\InvalidAuthor;
use App\Domain\Book\Exception\InvalidISBN;
use App\Domain\Book\Exception\InvalidTitle;
use App\Domain\Book\ValueObject\Author;
use App\Domain\Book\ValueObject\ISBN;
use App\Domain\Book\ValueObject\Title;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ValueObjectTest extends TestCase
{
    #[Test]
    public function createsValidISBN(): void
    {
        $isbn = ISBN::from('9780134494166');

        self::assertSame('9780134494166', $isbn->value());
    }

    #[Test]
    public function acceptsISBNWithDashes(): void
    {
        $isbn = ISBN::from('978-0-13-449416-6');

        self::assertSame('9780134494166', $isbn->value());
    }

    #[Test]
    public function rejectsMalformedISBN(): void
    {
        $this->expectException(InvalidISBN::class);

        ISBN::from('123');
    }

    #[Test]
    public function rejectsISBNWithInvalidCheckDigit(): void
    {
        $this->expectException(InvalidISBN::class);

        ISBN::from('9780134494160');
    }

    #[Test]
    public function comparesISBNEquality(): void
    {
        $isbn1 = ISBN::from('9780134494166');
        $isbn2 = ISBN::from('978-0-13-449416-6');

        self::assertTrue($isbn1->equals($isbn2));
    }

    #[Test]
    public function createsValidTitle(): void
    {
        $title = Title::from('Clean Architecture');

        self::assertSame('Clean Architecture', $title->value());
    }

    #[Test]
    public function rejectsEmptyTitle(): void
    {
        $this->expectException(InvalidTitle::class);

        Title::from('');
    }

    #[Test]
    public function rejectsTooLongTitle(): void
    {
        $this->expectException(InvalidTitle::class);

        Title::from(str_repeat('a', 256));
    }

    #[Test]
    public function createsValidAuthor(): void
    {
        $author = Author::from('Robert C. Martin');

        self::assertSame('Robert C. Martin', $author->value());
    }

    #[Test]
    public function rejectsEmptyAuthor(): void
    {
        $this->expectException(InvalidAuthor::class);

        Author::from('');
    }
}
