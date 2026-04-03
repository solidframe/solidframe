<?php

declare(strict_types=1);

namespace SolidFrame\Core\Tests\Pipeline;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Pipeline\Pipeline;

final class PipelineTest extends TestCase
{
    #[Test]
    public function processesPayloadThroughStages(): void
    {
        $pipeline = (new Pipeline())
            ->pipe(static fn(int $value): int => $value + 1)
            ->pipe(static fn(int $value): int => $value * 2);

        self::assertSame(6, $pipeline->process(2));
    }

    #[Test]
    public function returnsPayloadUnchangedWhenEmpty(): void
    {
        $pipeline = new Pipeline();

        self::assertSame('hello', $pipeline->process('hello'));
    }

    #[Test]
    public function isImmutable(): void
    {
        $pipeline1 = new Pipeline();
        $pipeline2 = $pipeline1->pipe(static fn(int $value): int => $value + 1);

        self::assertSame(5, $pipeline1->process(5));
        self::assertSame(6, $pipeline2->process(5));
    }

    #[Test]
    public function canBeInvokedAsCallable(): void
    {
        $pipeline = (new Pipeline())
            ->pipe(static fn(string $value): string => strtoupper($value));

        self::assertSame('HELLO', $pipeline('hello'));
    }
}
