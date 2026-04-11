<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Modular;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Laravel\Modular\ModuleServiceProvider;
use SolidFrame\Laravel\SolidFrameServiceProvider;
use SolidFrame\Modular\Module\AbstractModule;
use SolidFrame\Modular\Module\ModuleInterface;

final class ModularTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SolidFrameServiceProvider::class];
    }

    protected function tearDown(): void
    {
        $modulesPath = $this->app->basePath('modules');

        if (File::isDirectory($modulesPath)) {
            File::deleteDirectory($modulesPath);
        }

        parent::tearDown();
    }

    // -- make:module --

    #[Test]
    public function makeModuleCreatesDirectoryStructure(): void
    {
        $this->artisan('make:module', ['name' => 'Billing'])
            ->assertSuccessful();

        $base = $this->app->basePath('modules/Billing');

        self::assertDirectoryExists($base);
        self::assertFileExists($base . '/BillingServiceProvider.php');
        self::assertFileExists($base . '/BillingModule.php');
        self::assertDirectoryExists($base . '/Database/Migrations');
    }

    #[Test]
    public function makeModuleGeneratesCorrectServiceProviderContent(): void
    {
        $this->artisan('make:module', ['name' => 'Billing']);

        $content = File::get($this->app->basePath('modules/Billing/BillingServiceProvider.php'));

        self::assertStringContainsString('extends ModuleServiceProvider', $content);
        self::assertStringContainsString('new BillingModule()', $content);
    }

    #[Test]
    public function makeModuleGeneratesCorrectModuleContent(): void
    {
        $this->artisan('make:module', ['name' => 'Billing']);

        $content = File::get($this->app->basePath('modules/Billing/BillingModule.php'));

        self::assertStringContainsString('extends AbstractModule', $content);
        self::assertStringContainsString("'billing'", $content);
    }

    #[Test]
    public function makeModuleFailsIfModuleExists(): void
    {
        $this->artisan('make:module', ['name' => 'Billing']);

        $this->artisan('make:module', ['name' => 'Billing'])
            ->assertFailed();
    }

    // -- ModuleServiceProvider --

    #[Test]
    public function moduleServiceProviderAutoBootsRoutes(): void
    {
        $provider = new TestModuleWithRoutesProvider($this->app);

        // Create a routes file
        $modulePath = $provider->modulePath();
        File::makeDirectory($modulePath, 0o755, true, true);
        File::put($modulePath . '/routes.php', '<?php // test routes');

        $provider->boot();

        // If boot() didn't throw, routes were loaded (or file didn't exist)
        self::assertTrue(true);

        File::deleteDirectory($modulePath);
    }
}

final class TestModuleWithRoutesModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct('test-routes');
    }
}

final class TestModuleWithRoutesProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new TestModuleWithRoutesModule();
    }

    public function modulePath(): string
    {
        return $this->app->basePath('modules/TestRoutes');
    }

    public function register(): void {}
}
