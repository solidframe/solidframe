<?php

declare(strict_types=1);

namespace SolidFrame\Core\Pipeline;

final readonly class Pipeline implements PipelineInterface
{
    /**
     * @param list<callable(mixed): mixed> $stages
     */
    public function __construct(
        private array $stages = [],
    ) {}

    public function pipe(callable $stage): self
    {
        return new self([...$this->stages, $stage]);
    }

    public function process(mixed $payload): mixed
    {
        return array_reduce(
            $this->stages,
            static fn(mixed $carry, callable $stage): mixed => $stage($carry),
            $payload,
        );
    }

    public function __invoke(mixed $payload): mixed
    {
        return $this->process($payload);
    }
}
