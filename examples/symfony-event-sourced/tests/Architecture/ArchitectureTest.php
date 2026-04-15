<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;

final class ArchitectureTest extends TestCase
{
    // -- Domain layer boundaries --

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
    public function domainDoesNotDependOnController(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('App\Controller');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function domainDoesNotDependOnSymfony(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain')
            ->doesNotDependOn('Symfony');
        $this->addToAssertionCount(1);
    }

    // -- Application layer boundaries --

    #[Test]
    public function applicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Application')
            ->doesNotDependOn('App\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function applicationDoesNotDependOnController(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Application')
            ->doesNotDependOn('App\Controller');
        $this->addToAssertionCount(1);
    }

    // -- Value object rules --

    #[Test]
    public function valueObjectsAreFinalReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Account/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    // -- Event rules --

    #[Test]
    public function eventsAreFinalReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Account/Event')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function eventsImplementDomainEventInterface(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Account/Event')
            ->implement(\SolidFrame\Core\Event\DomainEventInterface::class);
        $this->addToAssertionCount(1);
    }

    // -- Exception rules --

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Domain/Account/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
        $this->addToAssertionCount(1);
    }

    // -- Presets --

    #[Test]
    public function dddPreset(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../../src/Domain/Account',
            'infrastructureDir' => __DIR__ . '/../../src/Infrastructure',
            'applicationDir' => __DIR__ . '/../../src/Application',
        ])->assert();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cqrsPreset(): void
    {
        Arch::preset('cqrs', [
            'commandDir' => __DIR__ . '/../../src/Application/Command',
            'queryDir' => __DIR__ . '/../../src/Application/Query',
        ])->assert();
        $this->addToAssertionCount(1);
    }
}
