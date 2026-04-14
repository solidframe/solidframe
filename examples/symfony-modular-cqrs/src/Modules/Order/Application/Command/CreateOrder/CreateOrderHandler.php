<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Command\CreateOrder;

use App\Modules\Order\Domain\Order;
use App\Modules\Order\Domain\OrderId;
use App\Modules\Order\Domain\Port\OrderRepository;
use App\Modules\Order\Domain\ValueObject\CustomerEmail;
use App\Modules\Order\Domain\ValueObject\OrderItem;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class CreateOrderHandler implements CommandHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(CreateOrder $command): void
    {
        $id = new OrderId($command->orderId);

        $items = array_map(
            fn (array $item) => new OrderItem(
                productId: $item['product_id'],
                quantity: $item['quantity'],
                unitPrice: $item['unit_price'],
            ),
            $command->items,
        );

        $order = Order::create($id, CustomerEmail::from($command->customerEmail), $items);

        $this->orders->save($order);

        foreach ($order->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
