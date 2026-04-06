<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Preset;

use SolidFrame\Archtest\Analyzer\ClassFinder;
use SolidFrame\Archtest\Analyzer\ClassInfo;

final readonly class DddPreset implements PresetInterface
{
    public function __construct(
        private string $domainDir,
        private ?string $infrastructureDir = null,
        private ?string $applicationDir = null,
    ) {}

    public function evaluate(): array
    {
        $violations = [];

        $domainClasses = $this->loadClasses($this->domainDir);

        if ($this->infrastructureDir !== null) {
            $infraNamespaces = $this->extractNamespaces($this->infrastructureDir);

            foreach ($domainClasses as $info) {
                foreach ($info->dependencies as $dep) {
                    foreach ($infraNamespaces as $ns) {
                        if (str_starts_with($dep, $ns)) {
                            $violations[] = sprintf('[DDD] %s depends on infrastructure: %s', $info->fqcn, $dep);
                        }
                    }
                }
            }
        }

        if ($this->applicationDir !== null) {
            $appNamespaces = $this->extractNamespaces($this->applicationDir);

            foreach ($domainClasses as $info) {
                foreach ($info->dependencies as $dep) {
                    foreach ($appNamespaces as $ns) {
                        if (str_starts_with($dep, $ns)) {
                            $violations[] = sprintf('[DDD] %s depends on application: %s', $info->fqcn, $dep);
                        }
                    }
                }
            }
        }

        foreach ($domainClasses as $info) {
            if (str_contains($info->fqcn, 'ValueObject') && !$info->isFinal && !$info->isInterface) {
                $violations[] = sprintf('[DDD] ValueObject %s is not final', $info->fqcn);
            }

            if (str_contains($info->fqcn, 'ValueObject') && !$info->isReadonly && !$info->isInterface) {
                $violations[] = sprintf('[DDD] ValueObject %s is not readonly', $info->fqcn);
            }
        }

        return $violations;
    }

    /** @return list<ClassInfo> */
    private function loadClasses(string $directory): array
    {
        return array_map(
            ClassInfo::fromFqcn(...),
            ClassFinder::inDirectory($directory),
        );
    }

    /** @return list<string> */
    private function extractNamespaces(string $directory): array
    {
        $classes = ClassFinder::inDirectory($directory);

        $namespaces = [];

        foreach ($classes as $fqcn) {
            $ns = implode('\\', array_slice(explode('\\', $fqcn), 0, -1));

            if ($ns !== '' && !in_array($ns, $namespaces, true)) {
                $namespaces[] = $ns;
            }
        }

        return $namespaces;
    }
}
