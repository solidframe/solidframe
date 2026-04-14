<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Archtest\Arch;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function orderDomainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Order/Domain')
            ->doesNotDependOn('Modules\Order\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderDomainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Order/Domain')
            ->doesNotDependOn('Modules\Order\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderDomainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Order/Domain')
            ->doesNotDependOn('Illuminate');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryDomainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Domain')
            ->doesNotDependOn('Modules\Inventory\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryDomainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Domain')
            ->doesNotDependOn('Modules\Inventory\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryDomainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Domain')
            ->doesNotDependOn('Illuminate');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentDomainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Payment/Domain')
            ->doesNotDependOn('Modules\Payment\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentDomainDoesNotDependOnApplication(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Payment/Domain')
            ->doesNotDependOn('Modules\Payment\Application');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentDomainDoesNotDependOnFramework(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Payment/Domain')
            ->doesNotDependOn('Illuminate');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderApplicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Order/Application')
            ->doesNotDependOn('Modules\Order\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryApplicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Application')
            ->doesNotDependOn('Modules\Inventory\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function paymentApplicationDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Payment/Application')
            ->doesNotDependOn('Modules\Payment\Infrastructure');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function modulesDoNotCrossDependOnDomainDirectly(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Domain')
            ->doesNotDependOn('Modules\Order')
            ->doesNotDependOn('Modules\Payment');

        Arch::assertThat(__DIR__ . '/../../modules/Payment/Domain')
            ->doesNotDependOn('Modules\Inventory');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function orderValueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Order/Domain/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function inventoryValueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Domain/ValueObject')
            ->areFinal()
            ->areReadonly();
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function exceptionsImplementSolidFrameException(): void
    {
        Arch::assertThat(__DIR__ . '/../../modules/Order/Domain/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);

        Arch::assertThat(__DIR__ . '/../../modules/Inventory/Domain/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);

        Arch::assertThat(__DIR__ . '/../../modules/Payment/Domain/Exception')
            ->implement(\SolidFrame\Core\Exception\SolidFrameException::class);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function dddPresetPasses(): void
    {
        foreach (['Order', 'Inventory', 'Payment'] as $module) {
            Arch::preset('ddd', [
                'domainDir' => __DIR__ . "/../../modules/{$module}/Domain",
                'infrastructureDir' => __DIR__ . "/../../modules/{$module}/Infrastructure",
                'applicationDir' => __DIR__ . "/../../modules/{$module}/Application",
            ])->assert();
        }
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cqrsPresetPasses(): void
    {
        foreach (['Order', 'Inventory', 'Payment'] as $module) {
            Arch::preset('cqrs', [
                'commandDir' => __DIR__ . "/../../modules/{$module}/Application/Command",
                'queryDir' => __DIR__ . "/../../modules/{$module}/Application/Query",
            ])->assert();
        }
        $this->addToAssertionCount(1);
    }
}
