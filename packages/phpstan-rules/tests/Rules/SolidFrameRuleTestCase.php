<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules;

use PHPStan\Testing\RuleTestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @template TRule of \PHPStan\Rules\Rule
 * @extends RuleTestCase<TRule>
 */
abstract class SolidFrameRuleTestCase extends RuleTestCase
{
    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        $tmpNeon = tempnam(sys_get_temp_dir(), 'phpstan_solidframe_') . '.neon';
        $dataDir = static::dataDirectory();
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dataDir),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        $scanLines = array_map(
            static fn(string $f): string => sprintf('        - %s', $f),
            $files,
        );

        file_put_contents($tmpNeon, sprintf(
            "parameters:\n    scanFiles:\n%s\n",
            implode("\n", $scanLines),
        ));

        return [$tmpNeon];
    }

    abstract protected static function dataDirectory(): string;
}
