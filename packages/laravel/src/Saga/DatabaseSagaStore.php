<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Saga;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use SolidFrame\Saga\Saga\SagaInterface;
use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\Store\SagaStoreInterface;

final readonly class DatabaseSagaStore implements SagaStoreInterface
{
    private ConnectionInterface $connection;

    public function __construct(
        DatabaseManager $db,
        private string $table = 'sagas',
        ?string $connection = null,
    ) {
        $this->connection = $db->connection($connection);
    }

    public function find(string $id): ?SagaInterface
    {
        $row = $this->connection
            ->table($this->table)
            ->where('id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        return $this->deserialize($row);
    }

    public function findByAssociation(string $sagaClass, Association $association): ?SagaInterface
    {
        $rows = $this->connection
            ->table($this->table)
            ->where('saga_type', $sagaClass)
            ->get();

        foreach ($rows as $row) {
            $associations = json_decode((string) $row->associations, true, 512, JSON_THROW_ON_ERROR);

            foreach ($associations as $assoc) {
                if ($assoc['key'] === $association->key && $assoc['value'] === $association->value) {
                    return $this->deserialize($row);
                }
            }
        }

        return null;
    }

    public function save(SagaInterface $saga): void
    {
        $associations = array_map(
            static fn(Association $a): array => ['key' => $a->key, 'value' => $a->value],
            $saga->associations(),
        );

        $this->connection->table($this->table)->updateOrInsert(
            ['id' => $saga->id()],
            [
                'saga_type' => $saga::class,
                'status' => $saga->status()->name,
                'associations' => json_encode($associations, JSON_THROW_ON_ERROR),
                'state' => serialize($saga),
                'updated_at' => now()->format('Y-m-d H:i:s'),
                'created_at' => now()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function delete(string $id): void
    {
        $this->connection
            ->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    private function deserialize(object $row): SagaInterface
    {
        return unserialize($row->state);
    }
}
