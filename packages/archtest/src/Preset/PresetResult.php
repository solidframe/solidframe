<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Preset;

use SolidFrame\Archtest\Exception\ArchViolationException;

final readonly class PresetResult
{
    public function __construct(
        private PresetInterface $preset,
    ) {}

    public function assert(): void
    {
        $violations = $this->preset->evaluate();

        if ($violations !== []) {
            throw ArchViolationException::forViolations($violations);
        }
    }
}
