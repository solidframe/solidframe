<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Listener;

use App\Modules\Inventory\Application\Command\ReleaseStock\ReleaseStock;
use App\Modules\Inventory\Domain\Event\StockReserved;
use App\Modules\Order\Application\Saga\OrderFulfillmentSaga;
use App\Modules\Payment\Application\Command\ChargePayment\ChargePayment;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\EventDriven\EventListener;
use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\Store\SagaStoreInterface;

final readonly class StockReservedListener implements EventListener
{
    public function __construct(
        private SagaStoreInterface $sagaStore,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(StockReserved $event): void
    {
        $saga = $this->sagaStore->findByAssociation(
            OrderFulfillmentSaga::class,
            new Association('orderId', $event->orderId),
        );

        if (!$saga instanceof OrderFulfillmentSaga) {
            return;
        }

        $saga->markStockReserved($event->reservedItems);

        $saga->registerCompensation(fn () => $this->commandBus->dispatch(
            new ReleaseStock(
                $event->orderId,
                $event->reservedItems,
            ),
        ));

        $this->sagaStore->save($saga);

        $this->commandBus->dispatch(new ChargePayment(
            orderId: $event->orderId,
            amount: $saga->totalAmount(),
            method: 'credit_card',
        ));
    }
}
