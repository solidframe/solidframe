<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Command\ConfirmOrder;

use SolidFrame\Cqrs\Command;

final readonly class ConfirmOrder implements Command
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
