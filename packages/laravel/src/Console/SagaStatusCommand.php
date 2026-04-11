<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\Command;
use SolidFrame\Saga\Store\SagaStoreInterface;

final class SagaStatusCommand extends Command
{
    protected $signature = 'solidframe:saga:status {id : The saga ID}';

    protected $description = 'Show the status of a saga by ID';

    public function handle(SagaStoreInterface $store): int
    {
        $saga = $store->find($this->argument('id'));

        if ($saga === null) {
            $this->error("Saga not found: {$this->argument('id')}");

            return self::FAILURE;
        }

        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $saga->id()],
                ['Type', $saga::class],
                ['Status', $saga->status()->name],
                ['Associations', implode(', ', array_map(
                    static fn(\SolidFrame\Saga\State\Association $a): string => "{$a->key}={$a->value}",
                    $saga->associations(),
                ))],
            ],
        );

        return self::SUCCESS;
    }
}
