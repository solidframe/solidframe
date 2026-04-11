<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\EventSourcing;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Snapshot\Snapshot;
use SolidFrame\EventSourcing\Snapshot\SnapshotStoreInterface;

final readonly class DatabaseSnapshotStore implements SnapshotStoreInterface
{
    private ConnectionInterface $connection;

    public function __construct(
        DatabaseManager $db,
        private string $table = 'snapshots',
        ?string $connection = null,
    ) {
        $this->connection = $db->connection($connection);
    }

    public function save(Snapshot $snapshot): void
    {
        $this->connection->table($this->table)->updateOrInsert(
            ['aggregate_id' => $snapshot->aggregateId],
            [
                'aggregate_type' => $snapshot->aggregateType,
                'version' => $snapshot->version,
                'state' => json_encode($snapshot->state, JSON_THROW_ON_ERROR),
                'created_at' => now()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function load(IdentityInterface $aggregateId): ?Snapshot
    {
        $row = $this->connection
            ->table($this->table)
            ->where('aggregate_id', $aggregateId->value())
            ->first();

        if ($row === null) {
            return null;
        }

        return new Snapshot(
            aggregateId: $row->aggregate_id,
            aggregateType: $row->aggregate_type,
            version: (int) $row->version,
            state: json_decode((string) $row->state, true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
