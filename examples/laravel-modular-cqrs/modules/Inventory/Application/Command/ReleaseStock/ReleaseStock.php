<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Command\ReleaseStock;

use SolidFrame\Cqrs\Command;

final readonly class ReleaseStock implements Command
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
