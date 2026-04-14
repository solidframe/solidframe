<?php

declare(strict_types=1);

namespace Modules\Order;

use Modules\Order\Domain\Port\OrderRepository;
use Modules\Order\Infrastructure\Persistence\Eloquent\EloquentOrderRepository;
use SolidFrame\Laravel\Modular\ModuleServiceProvider;
use SolidFrame\Modular\Module\ModuleInterface;

final class OrderServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new OrderModule();
    }

    public function register(): void
    {
        parent::register();

        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
    }
}
