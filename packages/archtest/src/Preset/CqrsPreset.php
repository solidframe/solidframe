<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Preset;

use SolidFrame\Archtest\Analyzer\ClassFinder;
use SolidFrame\Archtest\Analyzer\ClassInfo;

final readonly class CqrsPreset implements PresetInterface
{
    public function __construct(
        private string $commandDir,
        private string $queryDir,
        private ?string $handlerDir = null,
    ) {}

    public function evaluate(): array
    {
        $violations = [];

        $violations = [...$violations, ...$this->checkImmutable($this->commandDir, 'Command')];
        $violations = [...$violations, ...$this->checkImmutable($this->queryDir, 'Query')];

        if ($this->handlerDir !== null) {
            $violations = [...$violations, ...$this->checkHandlerPairing($this->commandDir, $this->handlerDir, 'Command')];
            $violations = [...$violations, ...$this->checkHandlerPairing($this->queryDir, $this->handlerDir, 'Query')];
        }

        return $violations;
    }

    /** @return list<string> */
    private function checkImmutable(string $directory, string $type): array
    {
        $violations = [];

        foreach (ClassFinder::inDirectory($directory) as $fqcn) {
            $info = ClassInfo::fromFqcn($fqcn);

            if ($info->isInterface) {
                continue;
            }

            if (!$info->isFinal) {
                $violations[] = sprintf('[CQRS] %s %s is not final', $type, $info->fqcn);
            }

            if (!$info->isReadonly) {
                $violations[] = sprintf('[CQRS] %s %s is not readonly', $type, $info->fqcn);
            }
        }

        return $violations;
    }

    /** @return list<string> */
    private function checkHandlerPairing(string $messageDir, string $handlerDir, string $type): array
    {
        $violations = [];
        $messages = ClassFinder::inDirectory($messageDir);
        $handlers = ClassFinder::inDirectory($handlerDir);

        $handlerShortNames = array_map(
            fn(string $fqcn): string => (ClassInfo::fromFqcn($fqcn))->shortName,
            $handlers,
        );

        foreach ($messages as $fqcn) {
            $info = ClassInfo::fromFqcn($fqcn);

            if ($info->isInterface) {
                continue;
            }

            $expectedHandler = $info->shortName . 'Handler';

            if (!in_array($expectedHandler, $handlerShortNames, true)) {
                $violations[] = sprintf('[CQRS] %s %s has no matching handler (%s)', $type, $info->fqcn, $expectedHandler);
            }
        }

        return $violations;
    }
}
