<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function domainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('App\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function domainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('App\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function domainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('Illuminate');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function applicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Application')
            ->doesNotDependOn('App\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function valueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Project/ValueObject')
            ->areFinal()
            ->areReadonly();

        Arch::assertThat(__DIR__ . '/../../app/Domain/Task/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Project/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);

        Arch::assertThat(__DIR__ . '/../../app/Domain/Task/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function dddPresetPasses(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../../app/Domain',
            'infrastructureDir' => __DIR__ . '/../../app/Infrastructure',
            'applicationDir' => __DIR__ . '/../../app/Application',
        ])->assert();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cqrsPresetPasses(): void
    {
        Arch::preset('cqrs', [
            'commandDir' => __DIR__ . '/../../app/Application/Command',
            'queryDir' => __DIR__ . '/../../app/Application/Query',
        ])->assert();
        $this->addToAssertionCount(1);
    }
}
