<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\Command;
use SolidFrame\Modular\Registry\ModuleRegistryInterface;

final class ModuleListCommand extends Command
{
    protected $signature = 'solidframe:module:list';

    protected $description = 'List all registered modules';

    public function handle(ModuleRegistryInterface $registry): int
    {
        $modules = $registry->all();

        if ($modules === []) {
            $this->info('No modules registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Dependencies'],
            array_map(static fn($module): array => [
                $module->name(),
                implode(', ', $module->dependsOn()) ?: '-',
            ], $modules),
        );

        return self::SUCCESS;
    }
}
