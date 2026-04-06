<?php

declare(strict_types=1);

namespace SolidFrame\Archtest;

use InvalidArgumentException;
use SolidFrame\Archtest\Expectation\ArchExpectation;
use SolidFrame\Archtest\Preset\CqrsPreset;
use SolidFrame\Archtest\Preset\DddPreset;
use SolidFrame\Archtest\Preset\EventDrivenPreset;
use SolidFrame\Archtest\Preset\ModularPreset;
use SolidFrame\Archtest\Preset\PresetInterface;
use SolidFrame\Archtest\Preset\PresetResult;

final class Arch
{
    public static function assertThat(string $directory): ArchExpectation
    {
        return new ArchExpectation($directory);
    }

    /** @param array<string, mixed> $config */
    public static function preset(string $name, array $config = []): PresetResult
    {
        $preset = match ($name) {
            'ddd' => new DddPreset(
                domainDir: $config['domainDir'],
                infrastructureDir: $config['infrastructureDir'] ?? null,
                applicationDir: $config['applicationDir'] ?? null,
            ),
            'cqrs' => new CqrsPreset(
                commandDir: $config['commandDir'],
                queryDir: $config['queryDir'],
                handlerDir: $config['handlerDir'] ?? null,
            ),
            'event-driven' => new EventDrivenPreset(
                eventDir: $config['eventDir'],
            ),
            'modular' => new ModularPreset(
                modulesDir: $config['modulesDir'],
                contractSubNamespace: $config['contractSubNamespace'] ?? 'Contract',
            ),
            default => throw new InvalidArgumentException(sprintf('Unknown preset: "%s"', $name)),
        };

        return new PresetResult($preset);
    }

    public static function presetFrom(PresetInterface $preset): PresetResult
    {
        return new PresetResult($preset);
    }
}
