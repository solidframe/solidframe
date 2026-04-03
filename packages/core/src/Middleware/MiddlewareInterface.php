<?php

declare(strict_types=1);

namespace SolidFrame\Core\Middleware;

interface MiddlewareInterface
{
    public function handle(object $message, callable $next): mixed;
}
