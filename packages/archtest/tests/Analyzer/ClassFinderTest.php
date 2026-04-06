<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Tests\Analyzer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Analyzer\ClassFinder;

final class ClassFinderTest extends TestCase
{
    #[Test]
    public function findsClassesInDirectory(): void
    {
        $classes = ClassFinder::inDirectory(__DIR__ . '/../Fixtures');

        self::assertNotEmpty($classes);
        self::assertContains(\SolidFrame\Archtest\Tests\Fixtures\FinalReadonlyClass::class, $classes);
        self::assertContains(\SolidFrame\Archtest\Tests\Fixtures\NonFinalClass::class, $classes);
    }

    #[Test]
    public function returnsEmptyForNonExistentDirectory(): void
    {
        self::assertSame([], ClassFinder::inDirectory('/nonexistent/path'));
    }

    #[Test]
    public function findsSortedFqcns(): void
    {
        $classes = ClassFinder::inDirectory(__DIR__ . '/../Fixtures');

        $sorted = $classes;
        sort($sorted);

        self::assertSame($sorted, $classes);
    }

    #[Test]
    public function findsInterfacesAndEnums(): void
    {
        $classes = ClassFinder::inDirectory(__DIR__ . '/../Fixtures');

        self::assertContains(\SolidFrame\Archtest\Tests\Fixtures\SomeInterface::class, $classes);
        self::assertContains(\SolidFrame\Archtest\Tests\Fixtures\SomeEnum::class, $classes);
    }
}
