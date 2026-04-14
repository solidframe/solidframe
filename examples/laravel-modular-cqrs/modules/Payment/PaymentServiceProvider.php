<?php

declare(strict_types=1);

namespace Modules\Payment;

use Modules\Payment\Domain\Port\PaymentRepository;
use Modules\Payment\Infrastructure\Persistence\Eloquent\EloquentPaymentRepository;
use SolidFrame\Laravel\Modular\ModuleServiceProvider;
use SolidFrame\Modular\Module\ModuleInterface;

final class PaymentServiceProvider extends ModuleServiceProvider
{
    public function module(): ModuleInterface
    {
        return new PaymentModule();
    }

    public function register(): void
    {
        parent::register();

        $this->app->bind(PaymentRepository::class, EloquentPaymentRepository::class);
    }
}
