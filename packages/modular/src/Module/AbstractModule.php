<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Module;

abstract class AbstractModule implements ModuleInterface
{
    /** @param list<string> $dependsOn */
    public function __construct(
        private readonly string $name,
        private readonly array $dependsOn = [],
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function dependsOn(): array
    {
        return $this->dependsOn;
    }
}
