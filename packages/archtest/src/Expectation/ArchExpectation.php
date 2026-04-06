<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Expectation;

use SolidFrame\Archtest\Analyzer\ClassFinder;
use SolidFrame\Archtest\Analyzer\ClassInfo;
use SolidFrame\Archtest\Exception\ArchViolationException;

final class ArchExpectation
{
    /** @var list<ClassInfo>|null */
    private ?array $classInfoCache = null;

    public function __construct(
        private readonly string $directory,
    ) {}

    public function areFinal(): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!$info->isFinal && !$info->isInterface && !$info->isEnum) {
                $violations[] = sprintf('%s is not final', $info->fqcn);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function areReadonly(): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!$info->isReadonly && !$info->isInterface && !$info->isEnum) {
                $violations[] = sprintf('%s is not readonly', $info->fqcn);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function areAbstract(): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!$info->isAbstract && !$info->isInterface) {
                $violations[] = sprintf('%s is not abstract', $info->fqcn);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function areInterfaces(): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!$info->isInterface) {
                $violations[] = sprintf('%s is not an interface', $info->fqcn);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function areEnums(): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!$info->isEnum) {
                $violations[] = sprintf('%s is not an enum', $info->fqcn);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function haveSuffix(string $suffix): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!str_ends_with($info->shortName, $suffix)) {
                $violations[] = sprintf('%s does not have suffix "%s"', $info->fqcn, $suffix);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function havePrefix(string $prefix): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if (!str_starts_with($info->shortName, $prefix)) {
                $violations[] = sprintf('%s does not have prefix "%s"', $info->fqcn, $prefix);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function implement(string $interface): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if ($info->isInterface) {
                continue;
            }

            if (!in_array($interface, $info->interfaces, true)) {
                $violations[] = sprintf('%s does not implement %s', $info->fqcn, $interface);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function extend(string $class): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            if ($info->isInterface) {
                continue;
            }

            if ($info->parentClass !== $class) {
                $violations[] = sprintf('%s does not extend %s', $info->fqcn, $class);
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    public function doesNotDependOn(string $namespace): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            foreach ($info->dependencies as $dependency) {
                if (str_starts_with($dependency, $namespace)) {
                    $violations[] = sprintf('%s depends on %s', $info->fqcn, $dependency);
                }
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    /** @param list<string> $namespaces */
    public function onlyDependsOn(array $namespaces): self
    {
        $violations = [];

        foreach ($this->classes() as $info) {
            foreach ($info->dependencies as $dependency) {
                $allowed = false;

                foreach ($namespaces as $ns) {
                    if (str_starts_with($dependency, $ns)) {
                        $allowed = true;

                        break;
                    }
                }

                if (!$allowed) {
                    $violations[] = sprintf('%s depends on %s which is not in the allowed list', $info->fqcn, $dependency);
                }
            }
        }

        $this->failIfViolations($violations);

        return $this;
    }

    /** @return list<ClassInfo> */
    private function classes(): array
    {
        if ($this->classInfoCache !== null) {
            return $this->classInfoCache;
        }

        $fqcns = ClassFinder::inDirectory($this->directory);
        $this->classInfoCache = array_map(
            ClassInfo::fromFqcn(...),
            $fqcns,
        );

        return $this->classInfoCache;
    }

    /** @param list<string> $violations */
    private function failIfViolations(array $violations): void
    {
        if ($violations !== []) {
            throw ArchViolationException::forViolations($violations);
        }
    }
}
