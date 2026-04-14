<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\InventoryServiceProvider;
use Modules\Order\OrderServiceProvider;
use Modules\Payment\PaymentServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(OrderServiceProvider::class);
        $this->app->register(InventoryServiceProvider::class);
        $this->app->register(PaymentServiceProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
