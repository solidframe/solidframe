<?php

declare(strict_types=1);

namespace SolidFrame\Core\Pipeline;

interface StageInterface
{
    public function __invoke(mixed $payload): mixed;
}
