<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Listener;

use App\Modules\Inventory\Domain\Event\StockReservationFailed;
use App\Modules\Order\Application\Saga\OrderFulfillmentSaga;
use SolidFrame\EventDriven\EventListener;
use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\Store\SagaStoreInterface;

final readonly class StockReservationFailedListener implements EventListener
{
    public function __construct(
        private SagaStoreInterface $sagaStore,
    ) {
    }

    public function __invoke(StockReservationFailed $event): void
    {
        $saga = $this->sagaStore->findByAssociation(
            OrderFulfillmentSaga::class,
            new Association('orderId', $event->orderId),
        );

        if (!$saga instanceof OrderFulfillmentSaga) {
            return;
        }

        $saga->markFailed();

        $this->sagaStore->save($saga);
    }
}
