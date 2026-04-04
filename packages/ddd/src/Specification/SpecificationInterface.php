<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Specification;

interface SpecificationInterface
{
    public function isSatisfiedBy(mixed $candidate): bool;

    public function and(self $other): self;

    public function or(self $other): self;

    public function not(): self;
}
