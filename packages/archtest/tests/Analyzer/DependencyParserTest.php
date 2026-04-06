<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Tests\Analyzer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Analyzer\DependencyParser;

final class DependencyParserTest extends TestCase
{
    #[Test]
    public function parsesUseStatements(): void
    {
        $deps = DependencyParser::parse(__DIR__ . '/../Fixtures/DependsOnExternal.php');

        self::assertContains('DateTimeImmutable', $deps);
    }

    #[Test]
    public function returnsEmptyForClassWithoutUseStatements(): void
    {
        $deps = DependencyParser::parse(__DIR__ . '/../Fixtures/NonFinalClass.php');

        self::assertSame([], $deps);
    }

    #[Test]
    public function returnsEmptyForNonExistentFile(): void
    {
        $deps = DependencyParser::parse('/nonexistent/file.php');

        self::assertSame([], $deps);
    }
}
