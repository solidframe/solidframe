<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Projection\AccountBalanceProjection;
use App\Infrastructure\Projection\TransactionHistoryProjection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final class RebuildProjectionsCommand extends Command
{
    /** @var string */
    protected $signature = 'solidframe:projection:rebuild';

    /** @var string */
    protected $description = 'Rebuild all projections by replaying events from the event store';

    public function handle(
        EventStoreInterface $eventStore,
        AccountBalanceProjection $balanceProjection,
        TransactionHistoryProjection $transactionProjection,
    ): int {
        $this->info('Resetting projections...');

        $balanceProjection->reset();
        $transactionProjection->reset();

        $this->info('Replaying events...');

        $rows = DB::table('event_store')->orderBy('id')->get();
        $count = 0;

        foreach ($rows as $row) {
            /** @var class-string<DomainEventInterface> $eventClass */
            $eventClass = $row->event_type;
            $payload = json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR);

            $reflection = new ReflectionClass($eventClass);
            $event = $reflection->newInstanceWithoutConstructor();

            foreach ($payload as $property => $value) {
                if (!$reflection->hasProperty($property)) {
                    continue;
                }

                $prop = $reflection->getProperty($property);
                $type = $prop->getType();

                if ($type instanceof \ReflectionNamedType && $type->getName() === \DateTimeImmutable::class && is_string($value)) {
                    $value = new \DateTimeImmutable($value);
                }

                $prop->setValue($event, $value);
            }

            $shortName = $reflection->getShortName();
            $method = 'on' . $shortName;

            if (method_exists($balanceProjection, $method)) {
                $balanceProjection->$method($event);
            }

            if (method_exists($transactionProjection, $method)) {
                $transactionProjection->$method($event);
            }

            $count++;
        }

        $this->info(sprintf('Rebuilt projections from %d events.', $count));

        return self::SUCCESS;
    }
}
