<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Registry;

use SolidFrame\Modular\Exception\CircularDependencyException;
use SolidFrame\Modular\Exception\ModuleNotFoundException;
use SolidFrame\Modular\Module\ModuleInterface;

final class InMemoryModuleRegistry implements ModuleRegistryInterface
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    public function register(ModuleInterface $module): void
    {
        $this->modules[$module->name()] = $module;
    }

    public function get(string $moduleName): ModuleInterface
    {
        return $this->modules[$moduleName] ?? throw ModuleNotFoundException::forName($moduleName);
    }

    public function has(string $moduleName): bool
    {
        return isset($this->modules[$moduleName]);
    }

    public function all(): array
    {
        return array_values($this->modules);
    }

    public function dependencyOrder(): array
    {
        $inDegree = [];
        $adjacency = [];

        foreach ($this->modules as $name => $module) {
            $inDegree[$name] ??= 0;
            $adjacency[$name] ??= [];

            foreach ($module->dependsOn() as $dependency) {
                $adjacency[$dependency][] = $name;
                $inDegree[$dependency] ??= 0;
                $inDegree[$name]++;
            }
        }

        $queue = [];

        foreach ($inDegree as $name => $degree) {
            if ($degree === 0) {
                $queue[] = $name;
            }
        }

        $sorted = [];

        while ($queue !== []) {
            $current = array_shift($queue);
            $sorted[] = $current;

            foreach ($adjacency[$current] as $neighbor) {
                $inDegree[$neighbor]--;

                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        if (count($sorted) !== count($inDegree)) {
            $remaining = array_diff(array_keys($inDegree), $sorted);

            throw CircularDependencyException::forModules(...array_values($remaining));
        }

        return array_values(array_filter(
            array_map(fn(string $name): ?ModuleInterface => $this->modules[$name] ?? null, $sorted),
            fn(?ModuleInterface $module): bool => $module !== null,
        ));
    }
}
