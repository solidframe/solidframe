<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Analyzer;

use Error;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ClassFinder
{
    /** @return list<string> FQCN listesi */
    public static function inDirectory(string $directory): array
    {
        $resolved = realpath($directory);

        if ($resolved === false) {
            return [];
        }

        $files = is_file($resolved)
            ? [$resolved]
            : self::findPhpFiles($resolved);

        self::ensureAllLoaded($files);

        $classes = [];

        foreach ($files as $filePath) {
            $fqcn = self::extractFqcn($filePath);

            if ($fqcn !== null) {
                $classes[] = $fqcn;
            }
        }

        sort($classes);

        return $classes;
    }

    /** @return list<string> */
    private static function findPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    private static function extractFqcn(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        $tokens = token_get_all($contents);
        $namespace = '';
        $className = null;
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            if (!is_array($tokens[$i])) {
                continue;
            }

            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = self::extractName($tokens, $i, $tokenCount);
            }

            if (in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_ENUM, T_TRAIT], true)) {
                $prev = self::findPreviousMeaningfulToken($tokens, $i);

                if ($prev === 'new') {
                    continue;
                }

                $className = self::extractSimpleName($tokens, $i, $tokenCount);

                break;
            }
        }

        if ($className === null) {
            return null;
        }

        return $namespace !== '' ? $namespace . '\\' . $className : $className;
    }

    /** @var array<string, true> */
    private static array $loadedFiles = [];

    /** @param list<string> $filePaths */
    private static function ensureAllLoaded(array $filePaths): void
    {
        $remaining = [];

        foreach ($filePaths as $filePath) {
            if (isset(self::$loadedFiles[$filePath])) {
                continue;
            }

            $remaining[] = $filePath;
        }

        $maxPasses = 3;

        for ($pass = 0; $pass < $maxPasses && $remaining !== []; $pass++) {
            $failed = [];

            foreach ($remaining as $filePath) {
                try {
                    require $filePath;
                    self::$loadedFiles[$filePath] = true;
                } catch (Error) {
                    $failed[] = $filePath;
                }
            }

            $remaining = $failed;
        }
    }

    /** @param list<array{int, string, int}|string> $tokens */
    private static function extractName(array $tokens, int &$i, int $tokenCount): string
    {
        $name = '';
        $i++;

        while ($i < $tokenCount) {
            $token = $tokens[$i];

            if ($token === ';' || $token === '{') {
                break;
            }

            if (is_array($token) && in_array($token[0], [T_NAME_QUALIFIED, T_STRING], true)) {
                $name .= $token[1];
            }

            $i++;
        }

        return $name;
    }

    /** @param list<array{int, string, int}|string> $tokens */
    private static function extractSimpleName(array $tokens, int &$i, int $tokenCount): ?string
    {
        $i++;

        while ($i < $tokenCount) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                return $tokens[$i][1];
            }

            if (is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                $i++;

                continue;
            }

            break;
        }

        return null;
    }

    /** @param list<array{int, string, int}|string> $tokens */
    private static function findPreviousMeaningfulToken(array $tokens, int $index): ?string
    {
        for ($j = $index - 1; $j >= 0; $j--) {
            if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                continue;
            }

            if (is_array($tokens[$j])) {
                return $tokens[$j][1];
            }

            return $tokens[$j];
        }

        return null;
    }
}
