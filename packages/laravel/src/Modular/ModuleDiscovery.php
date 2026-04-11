<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Modular;

use Symfony\Component\Finder\Finder;

final class ModuleDiscovery
{
    /**
     * Scan a directory for classes that extend ModuleServiceProvider.
     *
     * Convention: each module directory contains a *ServiceProvider.php file.
     * Example: modules/Billing/BillingServiceProvider.php
     *
     * @return list<class-string<ModuleServiceProvider>>
     */
    public static function within(string $modulesPath, string $namespace): array
    {
        if (! is_dir($modulesPath)) {
            return [];
        }

        $providers = [];

        $finder = Finder::create()
            ->files()
            ->name('*ServiceProvider.php')
            ->depth('== 1') // Only look one level deep (modules/Billing/XxxServiceProvider.php)
            ->in($modulesPath);

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePath(); // e.g. "Billing"
            $className = $file->getBasename('.php');   // e.g. "BillingServiceProvider"

            $fqcn = $namespace . '\\' . $relativePath . '\\' . $className;

            if (! class_exists($fqcn)) {
                continue;
            }

            if (! is_subclass_of($fqcn, ModuleServiceProvider::class)) {
                continue;
            }

            $providers[] = $fqcn;
        }

        return $providers;
    }
}
