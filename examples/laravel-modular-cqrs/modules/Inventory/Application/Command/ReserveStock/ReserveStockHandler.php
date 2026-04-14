<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Command\ReserveStock;

use Modules\Inventory\Domain\Event\StockReservationFailed;
use Modules\Inventory\Domain\Event\StockReserved;
use Modules\Inventory\Domain\Exception\InsufficientStockException;
use Modules\Inventory\Domain\Port\ProductRepository;
use Modules\Inventory\Domain\ProductId;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class ReserveStockHandler implements CommandHandler
{
    public function __construct(
        private ProductRepository $products,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ReserveStock $command): void
    {
        try {
            foreach ($command->items as $item) {
                $product = $this->products->find(new ProductId($item['product_id']));
                $product->reserveStock($item['quantity']);
                $this->products->save($product);
            }

            $this->eventBus->dispatch(new StockReserved(
                orderId: $command->orderId,
                reservedItems: $command->items,
            ));
        } catch (InsufficientStockException $e) {
            $this->eventBus->dispatch(new StockReservationFailed(
                orderId: $command->orderId,
                reason: $e->getMessage(),
            ));
        }
    }
}
