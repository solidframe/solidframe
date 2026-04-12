<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function domainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('App\Infrastructure');
    }

    #[Test]
    public function domainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('App\Application');
    }

    #[Test]
    public function domainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('Symfony');
    }

    #[Test]
    public function valueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Book/ValueObject')
            ->areFinal()
            ->areReadonly();
    }

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Book/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
    }

    #[Test]
    public function dddPresetPasses(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../../src/Domain',
            'infrastructureDir' => __DIR__ . '/../../src/Infrastructure',
            'applicationDir' => __DIR__ . '/../../src/Application',
        ])->assert();
    }
}
