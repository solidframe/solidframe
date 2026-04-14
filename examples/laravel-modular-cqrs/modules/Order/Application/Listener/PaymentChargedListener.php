<?php

declare(strict_types=1);

namespace Modules\Order\Application\Listener;

use Modules\Order\Application\Command\ConfirmOrder\ConfirmOrder;
use Modules\Order\Application\Saga\OrderFulfillmentSaga;
use Modules\Payment\Domain\Event\PaymentCharged;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\EventDriven\EventListener;
use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\Store\SagaStoreInterface;

final readonly class PaymentChargedListener implements EventListener
{
    public function __construct(
        private SagaStoreInterface $sagaStore,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(PaymentCharged $event): void
    {
        $saga = $this->sagaStore->findByAssociation(
            OrderFulfillmentSaga::class,
            new Association('orderId', $event->orderId),
        );

        if (!$saga instanceof OrderFulfillmentSaga) {
            return;
        }

        $saga->markCompleted();

        $this->sagaStore->save($saga);

        $this->commandBus->dispatch(new ConfirmOrder($event->orderId));
    }
}
