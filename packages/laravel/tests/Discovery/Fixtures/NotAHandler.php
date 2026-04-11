<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Discovery\Fixtures;

final class NotAHandler
{
    /** @param CreateOrderCommand $command */
    public function __invoke(CreateOrderCommand $command): void {}
}
