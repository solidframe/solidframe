<?php

declare(strict_types=1);

namespace Modules\Inventory;

use SolidFrame\Modular\Module\AbstractModule;

final class InventoryModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct('inventory');
    }
}
