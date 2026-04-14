<?php

declare(strict_types=1);

namespace Modules\Inventory;

use Modules\Inventory\Domain\Port\ProductRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\EloquentProductRepository;
use SolidFrame\Laravel\Modular\ModuleServiceProvider;
use SolidFrame\Modular\Module\ModuleInterface;

final class InventoryServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new InventoryModule();
    }

    public function register(): void
    {
        parent::register();

        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
    }
}
