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
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function domainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('App\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function domainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('Symfony');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function applicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Application')
            ->doesNotDependOn('App\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function valueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Project/ValueObject')
            ->areFinal()
            ->areReadonly();

        Arch::assertThat(__DIR__ . '/../../src/Domain/Task/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Project/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);

        Arch::assertThat(__DIR__ . '/../../src/Domain/Task/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function dddPresetPasses(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../../src/Domain',
            'infrastructureDir' => __DIR__ . '/../../src/Infrastructure',
            'applicationDir' => __DIR__ . '/../../src/Application',
        ])->assert();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cqrsPresetPasses(): void
    {
        Arch::preset('cqrs', [
            'commandDir' => __DIR__ . '/../../src/Application/Command',
            'queryDir' => __DIR__ . '/../../src/Application/Query',
        ])->assert();
        $this->addToAssertionCount(1);
    }
}
