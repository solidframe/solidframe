<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\EventSourcing;

use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Exception\ConcurrencyException;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final readonly class DatabaseEventStore implements EventStoreInterface
{
    private ConnectionInterface $connection;

    public function __construct(
        DatabaseManager $db,
        private string $table = 'event_store',
        ?string $connection = null,
    ) {
        $this->connection = $db->connection($connection);
    }

    public function persist(IdentityInterface $aggregateId, int $expectedVersion, array $events): void
    {
        $id = $aggregateId->value();

        $currentVersion = (int) $this->connection
            ->table($this->table)
            ->where('aggregate_id', $id)
            ->max('version') ?? 0;

        if ($currentVersion !== $expectedVersion) {
            throw ConcurrencyException::forAggregate($id, $expectedVersion, $currentVersion);
        }

        $version = $expectedVersion;

        $rows = array_map(function (DomainEventInterface $event) use ($id, &$version): array {
            $version++;

            return [
                'aggregate_id' => $id,
                'aggregate_type' => $event::class, // Will be overridden by caller context if needed
                'version' => $version,
                'event_type' => $event::class,
                'payload' => json_encode($this->serializeEvent($event), JSON_THROW_ON_ERROR),
                'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s.u'),
            ];
        }, $events);

        $this->connection->table($this->table)->insert($rows);
    }

    public function load(IdentityInterface $aggregateId): array
    {
        $rows = $this->connection
            ->table($this->table)
            ->where('aggregate_id', $aggregateId->value())
            ->orderBy('version')
            ->get();

        return $rows->map(fn(object $row): \SolidFrame\Core\Event\DomainEventInterface => $this->deserializeEvent($row))->all();
    }

    public function loadFromVersion(IdentityInterface $aggregateId, int $fromVersion): array
    {
        $rows = $this->connection
            ->table($this->table)
            ->where('aggregate_id', $aggregateId->value())
            ->where('version', '>', $fromVersion)
            ->orderBy('version')
            ->get();

        return $rows->map(fn(object $row): \SolidFrame\Core\Event\DomainEventInterface => $this->deserializeEvent($row))->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEvent(DomainEventInterface $event): array
    {
        $reflection = new ReflectionClass($event);
        $data = [];

        foreach ($reflection->getProperties() as $prop) {
            $value = $prop->getValue($event);

            if ($value instanceof DateTimeImmutable) {
                $value = $value->format('Y-m-d H:i:s.u');
            }

            $data[$prop->getName()] = $value;
        }

        return $data;
    }

    private function deserializeEvent(object $row): DomainEventInterface
    {
        /** @var class-string<DomainEventInterface> $eventClass */
        $eventClass = $row->event_type;
        $payload = json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR);

        $reflection = new ReflectionClass($eventClass);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($payload as $property => $value) {
            if (! $reflection->hasProperty($property)) {
                continue;
            }

            $prop = $reflection->getProperty($property);
            $prop->setValue($instance, $this->castPropertyValue($prop, $value));
        }

        return $instance;
    }

    private function castPropertyValue(ReflectionProperty $prop, mixed $value): mixed
    {
        $type = $prop->getType();

        if ($type instanceof ReflectionNamedType && $type->getName() === DateTimeImmutable::class && is_string($value)) {
            return new DateTimeImmutable($value);
        }

        return $value;
    }
}
