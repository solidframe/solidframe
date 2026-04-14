<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\ValueObject;

final readonly class OrderItem
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public int $unitPrice,
    ) {
    }

    public function lineTotal(): int
    {
        return $this->quantity * $this->unitPrice;
    }

    public function equals(self $other): bool
    {
        return $this->productId === $other->productId
            && $this->quantity === $other->quantity
            && $this->unitPrice === $other->unitPrice;
    }
}
