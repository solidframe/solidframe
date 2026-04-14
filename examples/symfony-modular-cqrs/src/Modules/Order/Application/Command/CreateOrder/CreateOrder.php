<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Command\CreateOrder;

use SolidFrame\Cqrs\Command;

final readonly class CreateOrder implements Command
{
    /**
     * @param list<array{product_id: string, quantity: int, unit_price: int}> $items
     */
    public function __construct(
        public string $orderId,
        public string $customerEmail,
        public array $items,
    ) {
    }
}
