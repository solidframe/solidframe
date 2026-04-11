<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Discovery\Fixtures;

use SolidFrame\EventDriven\EventListener;

final class SendOrderConfirmationListener implements EventListener
{
    public function __invoke(OrderCreatedEvent $event): void {}
}
