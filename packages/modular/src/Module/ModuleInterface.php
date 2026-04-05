<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Module;

interface ModuleInterface
{
    public function name(): string;

    /** @return list<string> */
    public function dependsOn(): array;
}
