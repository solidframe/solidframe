<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Analyzer;

use ReflectionClass;

final readonly class ClassInfo
{
    /**
     * @param list<string> $interfaces
     * @param list<string> $dependencies
     */
    public function __construct(
        public string $fqcn,
        public string $shortName,
        public bool $isFinal,
        public bool $isReadonly,
        public bool $isAbstract,
        public bool $isInterface,
        public bool $isEnum,
        public ?string $parentClass,
        public array $interfaces,
        public array $dependencies,
        public string $filePath,
    ) {}

    public static function fromFqcn(string $fqcn): self
    {
        /** @var class-string $fqcn */
        $reflection = new ReflectionClass($fqcn);
        $isEnum = enum_exists($fqcn);

        $parentClass = $reflection->getParentClass();
        $interfaces = array_values(array_map(
            fn(ReflectionClass $r): string => $r->getName(),
            $reflection->getInterfaces(),
        ));

        $filePath = $reflection->getFileName() ?: '';
        $dependencies = $filePath !== '' ? DependencyParser::parse($filePath) : [];

        return new self(
            fqcn: $fqcn,
            shortName: $reflection->getShortName(),
            isFinal: $reflection->isFinal(),
            isReadonly: $reflection->isReadonly(),
            isAbstract: $reflection->isAbstract() && !$reflection->isInterface(),
            isInterface: $reflection->isInterface(),
            isEnum: $isEnum,
            parentClass: $parentClass !== false ? $parentClass->getName() : null,
            interfaces: $interfaces,
            dependencies: $dependencies,
            filePath: $filePath,
        );
    }
}
