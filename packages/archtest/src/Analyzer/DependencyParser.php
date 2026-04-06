<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Analyzer;

final class DependencyParser
{
    /** @return list<string> */
    public static function parse(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [];
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return [];
        }

        $tokens = token_get_all($contents);
        $dependencies = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            if (!is_array($tokens[$i])) {
                continue;
            }

            if ($tokens[$i][0] !== T_USE) {
                continue;
            }

            // Skip `use` inside closures/trait imports (preceded by `)` or class body `{`)
            $prevMeaningful = self::findPreviousMeaningfulToken($tokens, $i);

            if ($prevMeaningful !== null && $prevMeaningful === ')') {
                continue;
            }

            $namespace = self::extractNamespace($tokens, $i, $tokenCount);

            if ($namespace !== '') {
                $dependencies[] = $namespace;
            }
        }

        return $dependencies;
    }

    /** @param list<array{int, string, int}|string> $tokens */
    private static function extractNamespace(array $tokens, int &$i, int $tokenCount): string
    {
        $namespace = '';
        $i++;

        while ($i < $tokenCount) {
            $token = $tokens[$i];

            if ($token === ';') {
                break;
            }

            if ($token === ',') {
                break;
            }

            if (is_array($token)) {
                if (in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_STRING], true)) {
                    $namespace .= $token[1];
                }

                if ($token[0] === T_AS) {
                    break;
                }

                if ($token[0] === T_FUNCTION || $token[0] === T_CONST) {
                    return '';
                }
            }

            $i++;
        }

        return ltrim($namespace, '\\');
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
