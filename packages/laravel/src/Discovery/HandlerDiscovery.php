<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Discovery;

use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\Finder\Finder;

final class HandlerDiscovery
{
    /**
     * Scan directories for classes implementing the given marker interface
     * and extract message => handler mappings from __invoke type hints.
     *
     * @param list<string> $directories
     * @param class-string $markerInterface
     * @return array<class-string, class-string> message class => handler class
     */
    public static function within(array $directories, string $markerInterface): array
    {
        $handlers = [];

        foreach (static::findClasses($directories, $markerInterface) as $handlerClass) {
            $messageClass = static::extractMessageClass($handlerClass);

            if ($messageClass !== null) {
                $handlers[$messageClass] = $handlerClass;
            }
        }

        return $handlers;
    }

    /**
     * Same as within() but returns event => list<listener> mapping
     * since multiple listeners can handle the same event.
     *
     * @param list<string> $directories
     * @param class-string $markerInterface
     * @return array<class-string, list<class-string>>
     */
    public static function listeners(array $directories, string $markerInterface): array
    {
        $listeners = [];

        foreach (static::findClasses($directories, $markerInterface) as $listenerClass) {
            $eventClass = static::extractMessageClass($listenerClass);

            if ($eventClass !== null) {
                $listeners[$eventClass][] = $listenerClass;
            }
        }

        return $listeners;
    }

    /**
     * @param list<string> $directories
     * @param class-string $markerInterface
     * @return list<class-string>
     */
    private static function findClasses(array $directories, string $markerInterface): array
    {
        $existingDirs = array_filter($directories, is_dir(...));

        if ($existingDirs === []) {
            return [];
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($existingDirs);

        $classes = [];

        foreach ($finder as $file) {
            $class = static::classFromFile($file->getRealPath());

            if ($class === null) {
                continue;
            }

            if (! is_subclass_of($class, $markerInterface)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * @param class-string $handlerClass
     * @return class-string|null
     */
    private static function extractMessageClass(string $handlerClass): ?string
    {
        $reflection = new ReflectionClass($handlerClass);

        if (! $reflection->hasMethod('__invoke')) {
            return null;
        }

        $invoke = $reflection->getMethod('__invoke');
        $params = $invoke->getParameters();

        if ($params === []) {
            return null;
        }

        $type = $params[0]->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        return $type->getName();
    }

    private static function classFromFile(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $class = null;

        $tokens = token_get_all($contents);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (! is_array($tokens[$i])) {
                continue;
            }

            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = static::extractAfterToken($tokens, $i, $count);
            }

            if ($tokens[$i][0] === T_CLASS) {
                // Skip ::class usage and anonymous classes
                if (isset($tokens[$i - 1]) && is_array($tokens[$i - 1]) && $tokens[$i - 1][0] === T_DOUBLE_COLON) {
                    continue;
                }

                $class = static::extractClassName($tokens, $i, $count);

                break; // Only first class per file
            }
        }

        if ($class === null) {
            return null;
        }

        $fqcn = $namespace !== null ? $namespace . '\\' . $class : $class;

        // Ensure class is autoloadable
        if (! class_exists($fqcn)) {
            return null;
        }

        return $fqcn;
    }

    /**
     * @param list<mixed> $tokens
     */
    private static function extractAfterToken(array $tokens, int &$i, int $count): string
    {
        $value = '';
        $i++; // skip the keyword token

        for (; $i < $count; $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                continue;
            }

            if (! is_array($tokens[$i]) && $tokens[$i] === ';') {
                break;
            }

            if (is_array($tokens[$i])) {
                $value .= $tokens[$i][1];
            }
        }

        return $value;
    }

    /**
     * @param list<mixed> $tokens
     */
    private static function extractClassName(array $tokens, int &$i, int $count): ?string
    {
        $i++; // skip T_CLASS

        for (; $i < $count; $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                continue;
            }

            if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                return $tokens[$i][1];
            }

            break;
        }

        return null;
    }
}
