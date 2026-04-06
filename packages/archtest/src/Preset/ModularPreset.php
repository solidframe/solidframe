<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Preset;

use SolidFrame\Archtest\Analyzer\ClassFinder;
use SolidFrame\Archtest\Analyzer\ClassInfo;

final readonly class ModularPreset implements PresetInterface
{
    public function __construct(
        private string $modulesDir,
        private string $contractSubNamespace = 'Contract',
    ) {}

    public function evaluate(): array
    {
        $violations = [];
        $modules = $this->discoverModules();

        foreach ($modules as $moduleName => $moduleDir) {
            $classes = array_map(
                ClassInfo::fromFqcn(...),
                ClassFinder::inDirectory($moduleDir),
            );

            foreach ($classes as $info) {
                foreach ($info->dependencies as $dep) {
                    foreach ($modules as $otherName => $otherDir) {
                        if ($otherName === $moduleName) {
                            continue;
                        }

                        $otherClasses = ClassFinder::inDirectory($otherDir);

                        foreach ($otherClasses as $otherFqcn) {
                            if ($dep !== $otherFqcn) {
                                continue;
                            }

                            if (!str_contains($otherFqcn, '\\' . $this->contractSubNamespace . '\\')) {
                                $violations[] = sprintf(
                                    '[Modular] %s (module: %s) depends on non-contract class %s (module: %s)',
                                    $info->fqcn,
                                    $moduleName,
                                    $otherFqcn,
                                    $otherName,
                                );
                            }
                        }
                    }
                }
            }
        }

        return $violations;
    }

    /** @return array<string, string> moduleName => directory */
    private function discoverModules(): array
    {
        $modulesDir = realpath($this->modulesDir);

        if ($modulesDir === false || !is_dir($modulesDir)) {
            return [];
        }

        $modules = [];
        $entries = scandir($modulesDir);

        if ($entries === false) {
            return [];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $modulesDir . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $modules[$entry] = $path;
            }
        }

        return $modules;
    }
}
