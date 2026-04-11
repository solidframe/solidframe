<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Console;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Laravel\SolidFrameServiceProvider;
use SolidFrame\Modular\Module\AbstractModule;
use SolidFrame\Modular\Registry\ModuleRegistryInterface;
use SolidFrame\Saga\Saga\AbstractSaga;
use SolidFrame\Saga\Store\SagaStoreInterface;

final class OperationalCommandsTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SolidFrameServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    // -- Module List --

    #[Test]
    public function moduleListShowsNoModulesMessage(): void
    {
        $this->artisan('solidframe:module:list')
            ->expectsOutput('No modules registered.')
            ->assertSuccessful();
    }

    #[Test]
    public function moduleListShowsRegisteredModules(): void
    {
        $registry = $this->app->make(ModuleRegistryInterface::class);

        $registry->register(new TestBillingModule());
        $registry->register(new TestCoreModule());

        $this->artisan('solidframe:module:list')
            ->expectsTable(
                ['Name', 'Dependencies'],
                [
                    ['billing', 'core'],
                    ['core', '-'],
                ],
            )
            ->assertSuccessful();
    }

    // -- Saga Status --

    #[Test]
    public function sagaStatusShowsNotFoundError(): void
    {
        $this->artisan('solidframe:saga:status', ['id' => 'non-existent-id'])
            ->expectsOutput('Saga not found: non-existent-id')
            ->assertFailed();
    }

    #[Test]
    public function sagaStatusShowsSagaDetails(): void
    {
        $store = $this->app->make(SagaStoreInterface::class);

        $saga = new TestOperationalSaga('test-saga-123');
        $saga->setOrderId('order-456');

        $store->save($saga);

        $this->artisan('solidframe:saga:status', ['id' => 'test-saga-123'])
            ->expectsTable(
                ['Property', 'Value'],
                [
                    ['ID', 'test-saga-123'],
                    ['Type', TestOperationalSaga::class],
                    ['Status', 'InProgress'],
                    ['Associations', 'orderId=order-456'],
                ],
            )
            ->assertSuccessful();
    }
}

final class TestBillingModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct('billing', ['core']);
    }
}

final class TestCoreModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct('core');
    }
}

final class TestOperationalSaga extends AbstractSaga
{
    public function setOrderId(string $orderId): void
    {
        $this->associateWith('orderId', $orderId);
    }
}
