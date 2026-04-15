<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;

final class ArchitectureTest extends TestCase
{
    // -- Domain layer boundaries --

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
    public function domainDoesNotDependOnHttp(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('App\Http');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function domainDoesNotDependOnLaravel(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain')
            ->doesNotDependOn('Illuminate');
        $this->addToAssertionCount(1);
    }

    // -- Application layer boundaries --

    #[Test]
    public function applicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Application')
            ->doesNotDependOn('App\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function applicationDoesNotDependOnHttp(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Application')
            ->doesNotDependOn('App\Http');
        $this->addToAssertionCount(1);
    }

    // -- Value object rules --

    #[Test]
    public function valueObjectsAreFinalReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Account/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    // -- Event rules --

    #[Test]
    public function eventsAreFinalReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Account/Event')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function eventsImplementDomainEventInterface(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Account/Event')
            ->implement(\SolidFrame\Core\Event\DomainEventInterface::class);
        $this->addToAssertionCount(1);
    }

    // -- Exception rules --

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../app/Domain/Account/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
        $this->addToAssertionCount(1);
    }

    // -- Presets --

    #[Test]
    public function dddPreset(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../../app/Domain/Account',
            'infrastructureDir' => __DIR__ . '/../../app/Infrastructure',
            'applicationDir' => __DIR__ . '/../../app/Application',
        ])->assert();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cqrsPreset(): void
    {
        Arch::preset('cqrs', [
            'commandDir' => __DIR__ . '/../../app/Application/Command',
            'queryDir' => __DIR__ . '/../../app/Application/Query',
        ])->assert();
        $this->addToAssertionCount(1);
    }
}
