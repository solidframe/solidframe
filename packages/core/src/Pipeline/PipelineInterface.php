<?php

declare(strict_types=1);

namespace SolidFrame\Core\Pipeline;

interface PipelineInterface
{
    public function pipe(callable $stage): self;

    public function process(mixed $payload): mixed;
}
