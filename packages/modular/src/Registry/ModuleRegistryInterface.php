<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Registry;

use SolidFrame\Modular\Module\ModuleInterface;

interface ModuleRegistryInterface
{
    public function register(ModuleInterface $module): void;

    public function get(string $moduleName): ModuleInterface;

    public function has(string $moduleName): bool;

    /** @return list<ModuleInterface> */
    public function all(): array;

    /** @return list<ModuleInterface> */
    public function dependencyOrder(): array;
}
