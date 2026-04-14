<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function orderDomainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Order/Domain')
            ->doesNotDependOn('App\Modules\Order\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderDomainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Order/Domain')
            ->doesNotDependOn('App\Modules\Order\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderDomainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Order/Domain')
            ->doesNotDependOn('Symfony');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryDomainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Domain')
            ->doesNotDependOn('App\Modules\Inventory\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryDomainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Domain')
            ->doesNotDependOn('App\Modules\Inventory\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryDomainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Domain')
            ->doesNotDependOn('Symfony');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentDomainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Payment/Domain')
            ->doesNotDependOn('App\Modules\Payment\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentDomainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Payment/Domain')
            ->doesNotDependOn('App\Modules\Payment\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentDomainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Payment/Domain')
            ->doesNotDependOn('Symfony');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderApplicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Order/Application')
            ->doesNotDependOn('App\Modules\Order\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryApplicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Application')
            ->doesNotDependOn('App\Modules\Inventory\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentApplicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Payment/Application')
            ->doesNotDependOn('App\Modules\Payment\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function modulesDoNotCrossDependOnDomainDirectly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Domain')
            ->doesNotDependOn('App\Modules\Order')
            ->doesNotDependOn('App\Modules\Payment');

        Arch::assertThat(__DIR__ . '/../../src/Modules/Payment/Domain')
            ->doesNotDependOn('App\Modules\Inventory');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderValueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Order/Domain/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryValueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Domain/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../src/Modules/Order/Domain/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);

        Arch::assertThat(__DIR__ . '/../../src/Modules/Inventory/Domain/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);

        Arch::assertThat(__DIR__ . '/../../src/Modules/Payment/Domain/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function dddPresetPasses(): void
    {
        foreach (['Order', 'Inventory', 'Payment'] as $module) {
            Arch::preset('ddd', [
                'domainDir' => __DIR__ . "/../../src/Modules/{$module}/Domain",
                'infrastructureDir' => __DIR__ . "/../../src/Modules/{$module}/Infrastructure",
                'applicationDir' => __DIR__ . "/../../src/Modules/{$module}/Application",
            ])->assert();
        }
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cqrsPresetPasses(): void
    {
        foreach (['Order', 'Inventory', 'Payment'] as $module) {
            Arch::preset('cqrs', [
                'commandDir' => __DIR__ . "/../../src/Modules/{$module}/Application/Command",
                'queryDir' => __DIR__ . "/../../src/Modules/{$module}/Application/Query",
            ])->assert();
        }
        $this->addToAssertionCount(1);
    }
}
