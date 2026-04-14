<?php

declare(strict_types=1);

namespace Modules\Order\Application\Command\CreateOrder;

use Modules\Order\Domain\Order;
use Modules\Order\Domain\OrderId;
use Modules\Order\Domain\Port\OrderRepository;
use Modules\Order\Domain\ValueObject\CustomerEmail;
use Modules\Order\Domain\ValueObject\OrderItem;
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
