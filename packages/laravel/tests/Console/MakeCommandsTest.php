<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Console;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Laravel\SolidFrameServiceProvider;

final class MakeCommandsTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SolidFrameServiceProvider::class];
    }

    protected function tearDown(): void
    {
        // Clean up generated files
        $appPath = $this->app->basePath('app');

        if (File::isDirectory($appPath)) {
            File::deleteDirectory($appPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function makeEntityCreatesFile(): void
    {
        $this->artisan('make:entity', ['name' => 'Order'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Domain/Order.php'));
    }

    #[Test]
    public function makeValueObjectCreatesFile(): void
    {
        $this->artisan('make:value-object', ['name' => 'Email'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Domain/Email.php'));
    }

    #[Test]
    public function makeAggregateRootCreatesFile(): void
    {
        $this->artisan('make:aggregate-root', ['name' => 'Order'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Domain/Order.php'));
    }

    #[Test]
    public function makeCqrsCommandCreatesFile(): void
    {
        $this->artisan('make:cqrs-command', ['name' => 'PlaceOrder'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Application/Command/PlaceOrder.php'));
    }

    #[Test]
    public function makeCqrsCommandWithHandlerCreatesBothFiles(): void
    {
        $this->artisan('make:cqrs-command', ['name' => 'PlaceOrder', '--handler' => true])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Application/Command/PlaceOrder.php'));
        self::assertFileExists($this->app->basePath('app/Application/Command/PlaceOrderHandler.php'));
    }

    #[Test]
    public function makeQueryCreatesFile(): void
    {
        $this->artisan('make:query', ['name' => 'GetOrder'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Application/Query/GetOrder.php'));
    }

    #[Test]
    public function makeQueryWithHandlerCreatesBothFiles(): void
    {
        $this->artisan('make:query', ['name' => 'GetOrder', '--handler' => true])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Application/Query/GetOrder.php'));
        self::assertFileExists($this->app->basePath('app/Application/Query/GetOrderHandler.php'));
    }

    #[Test]
    public function makeDomainEventCreatesFile(): void
    {
        $this->artisan('make:domain-event', ['name' => 'OrderPlaced'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Domain/Event/OrderPlaced.php'));
    }

    #[Test]
    public function makeEventListenerCreatesFile(): void
    {
        $this->artisan('make:event-listener', ['name' => 'SendConfirmation'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Application/Listener/SendConfirmation.php'));
    }

    #[Test]
    public function makeSagaCreatesFile(): void
    {
        $this->artisan('make:saga', ['name' => 'OrderSaga'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Application/Saga/OrderSaga.php'));
    }

    #[Test]
    public function generatedEntityHasCorrectContent(): void
    {
        $this->artisan('make:entity', ['name' => 'Order']);

        $content = File::get($this->app->basePath('app/Domain/Order.php'));

        self::assertStringContainsString('final class Order extends AbstractEntity', $content);
        self::assertStringContainsString('namespace App\Domain;', $content);
    }

    #[Test]
    public function generatedCommandHandlerHasTypedInvoke(): void
    {
        $this->artisan('make:cqrs-command', ['name' => 'PlaceOrder', '--handler' => true]);

        $content = File::get($this->app->basePath('app/Application/Command/PlaceOrderHandler.php'));

        self::assertStringContainsString('PlaceOrder $command', $content);
        self::assertStringContainsString('implements CommandHandler', $content);
    }

    #[Test]
    public function supportsSubdirectories(): void
    {
        $this->artisan('make:entity', ['name' => 'Order/OrderItem'])
            ->assertSuccessful();

        self::assertFileExists($this->app->basePath('app/Domain/Order/OrderItem.php'));
    }
}
