<?php

declare(strict_types=1);

namespace SolidFrame\Saga\State;

final readonly class Association
{
    public function __construct(
        public string $key,
        public string $value,
    ) {}

    public function equals(self $other): bool
    {
        return $this->key === $other->key
            && $this->value === $other->value;
    }
}
