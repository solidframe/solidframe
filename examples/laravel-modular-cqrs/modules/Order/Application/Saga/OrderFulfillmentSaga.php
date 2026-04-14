<?php

declare(strict_types=1);

namespace Modules\Order\Application\Saga;

use SolidFrame\Saga\Saga\AbstractSaga;

final class OrderFulfillmentSaga extends AbstractSaga
{
    private int $totalAmount = 0;
    /** @var list<array{product_id: string, quantity: int}> */
    private array $reservedItems = [];

    public function start(string $orderId, int $totalAmount): void
    {
        $this->associateWith('orderId', $orderId);
        $this->totalAmount = $totalAmount;
    }

    /** @param list<array{product_id: string, quantity: int}> $reservedItems */
    public function markStockReserved(array $reservedItems): void
    {
        $this->reservedItems = $reservedItems;
    }

    public function registerCompensation(callable $compensation): void
    {
        $this->addCompensation($compensation);
    }

    public function totalAmount(): int
    {
        return $this->totalAmount;
    }

    /** @return list<array{product_id: string, quantity: int}> */
    public function reservedItems(): array
    {
        return $this->reservedItems;
    }

    public function orderId(): ?string
    {
        foreach ($this->associations() as $assoc) {
            if ($assoc->key === 'orderId') {
                return $assoc->value;
            }
        }

        return null;
    }

    public function markCompleted(): void
    {
        $this->complete();
    }

    public function markFailed(): void
    {
        $this->fail();
    }
}
