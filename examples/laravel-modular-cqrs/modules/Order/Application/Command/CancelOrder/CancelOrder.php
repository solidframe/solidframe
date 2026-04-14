<?php

declare(strict_types=1);

namespace Modules\Order\Application\Command\CancelOrder;

use SolidFrame\Cqrs\Command;

final readonly class CancelOrder implements Command
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
