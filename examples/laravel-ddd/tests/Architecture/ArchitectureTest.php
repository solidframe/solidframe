<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;
use SolidFrame\Ddd\ValueObject\ValueObjectInterface;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function domainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('App\Infrastructure');
    }

    #[Test]
    public function domainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('App\Application');
    }

    #[Test]
    public function domainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('Illuminate');
    }

    #[Test]
    public function valueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Book/ValueObject')
            ->areFinal()
            ->areReadonly();
    }

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Book/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
    }

    #[Test]
    public function dddPresetPasses(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../../app/Domain',
            'infrastructureDir' => __DIR__ . '/../../app/Infrastructure',
            'applicationDir' => __DIR__ . '/../../app/Application',
        ])->assert();
    }
}
