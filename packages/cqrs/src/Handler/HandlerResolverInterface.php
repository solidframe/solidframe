<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Handler;

interface HandlerResolverInterface
{
    public function resolve(object $message): callable;
}
