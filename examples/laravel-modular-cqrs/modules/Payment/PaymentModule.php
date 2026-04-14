<?php

declare(strict_types=1);

namespace Modules\Payment;

use SolidFrame\Modular\Module\AbstractModule;

final class PaymentModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct('payment', ['order']);
    }
}
