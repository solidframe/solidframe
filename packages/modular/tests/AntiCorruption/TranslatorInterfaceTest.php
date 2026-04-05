<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Tests\AntiCorruption;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Modular\AntiCorruption\TranslatorInterface;

final class TranslatorInterfaceTest extends TestCase
{
    #[Test]
    public function translatesSourceToTarget(): void
    {
        $translator = new class implements TranslatorInterface {
            public function translate(object $source): \SolidFrame\Modular\Tests\AntiCorruption\TargetDto
            {
                return new TargetDto($source->name . ' (translated)');
            }
        };

        $source = new SourceDto('billing');
        $result = $translator->translate($source);

        self::assertInstanceOf(TargetDto::class, $result);
        self::assertSame('billing (translated)', $result->value);
    }
}

final readonly class SourceDto
{
    public function __construct(public string $name) {}
}

final readonly class TargetDto
{
    public function __construct(public string $value) {}
}
