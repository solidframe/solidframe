<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Specification;

final class NotSpecification extends AbstractSpecification
{
    public function __construct(
        private readonly SpecificationInterface $spec,
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return !$this->spec->isSatisfiedBy($candidate);
    }
}
