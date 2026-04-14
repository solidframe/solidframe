<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Command\ReserveStock;

use SolidFrame\Cqrs\Command;

final readonly class ReserveStock implements Command
{
    /**
     * @param list<array{product_id: string, quantity: int}> $items
     */
    public function __construct(
        public string $orderId,
        public array $items,
    ) {
    }
}
