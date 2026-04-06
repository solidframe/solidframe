<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Preset;

interface PresetInterface
{
    /** @return list<string> Violation messages, empty = success */
    public function evaluate(): array;
}
