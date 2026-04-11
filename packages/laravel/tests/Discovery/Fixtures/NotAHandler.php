<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Discovery\Fixtures;

final class NotAHandler
{
    public function __invoke(CreateOrderCommand $command): void {}
}
