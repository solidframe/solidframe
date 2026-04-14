<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Command\ReleaseStock;

use Modules\Inventory\Domain\Event\StockReleased;
use Modules\Inventory\Domain\Port\ProductRepository;
use Modules\Inventory\Domain\ProductId;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class ReleaseStockHandler implements CommandHandler
{
    public function __construct(
        private ProductRepository $products,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ReleaseStock $command): void
    {
        foreach ($command->items as $item) {
            $product = $this->products->find(new ProductId($item['product_id']));
            $product->releaseStock($item['quantity']);
            $this->products->save($product);
        }

        $this->eventBus->dispatch(new StockReleased(orderId: $command->orderId));
    }
}
