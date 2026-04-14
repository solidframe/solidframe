<?php

declare(strict_types=1);

namespace Modules\Order\Application\Command\ConfirmOrder;

use Modules\Order\Domain\OrderId;
use Modules\Order\Domain\Port\OrderRepository;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class ConfirmOrderHandler implements CommandHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ConfirmOrder $command): void
    {
        $order = $this->orders->find(new OrderId($command->orderId));

        $order->confirm();

        $this->orders->save($order);

        foreach ($order->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
