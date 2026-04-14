<?php

declare(strict_types=1);

namespace Modules\Order;

use SolidFrame\Modular\Module\AbstractModule;

final class OrderModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct('order');
    }
}
