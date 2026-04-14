<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Listener;

use App\Modules\Inventory\Application\Command\ReserveStock\ReserveStock;
use App\Modules\Order\Application\Command\CancelOrder\CancelOrder;
use App\Modules\Order\Application\Saga\OrderFulfillmentSaga;
use App\Modules\Order\Domain\Event\OrderCreated;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\EventDriven\EventListener;
use SolidFrame\Saga\Store\SagaStoreInterface;

final readonly class OrderCreatedStartSagaListener implements EventListener
{
    public function __construct(
        private SagaStoreInterface $sagaStore,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(OrderCreated $event): void
    {
        $saga = new OrderFulfillmentSaga();
        $saga->start($event->orderId, $event->totalAmount);

        $saga->registerCompensation(fn () => $this->commandBus->dispatch(
            new CancelOrder($event->orderId),
        ));

        $this->sagaStore->save($saga);

        $items = array_map(fn (array $item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ], $event->items);

        $this->commandBus->dispatch(new ReserveStock($event->orderId, $items));
    }
}
