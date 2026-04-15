<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

final readonly class SchemaManager
{
    public function __construct(private Connection $connection) {}

    public function createSchema(): void
    {
        $schema = new Schema();

        // Event store
        $eventStore = $schema->createTable('event_store');
        $eventStore->addColumn('id', 'integer', ['autoincrement' => true]);
        $eventStore->addColumn('aggregate_id', 'string', ['length' => 255]);
        $eventStore->addColumn('aggregate_type', 'string', ['length' => 255]);
        $eventStore->addColumn('version', 'integer', ['unsigned' => true]);
        $eventStore->addColumn('event_type', 'string', ['length' => 255]);
        $eventStore->addColumn('payload', 'text');
        $eventStore->addColumn('occurred_at', 'string', ['length' => 32]);
        $eventStore->setPrimaryKey(['id']);
        $eventStore->addUniqueIndex(['aggregate_id', 'version']);
        $eventStore->addIndex(['aggregate_id']);

        // Snapshot store
        $snapshotStore = $schema->createTable('snapshot_store');
        $snapshotStore->addColumn('id', 'integer', ['autoincrement' => true]);
        $snapshotStore->addColumn('aggregate_id', 'string', ['length' => 255]);
        $snapshotStore->addColumn('aggregate_type', 'string', ['length' => 255]);
        $snapshotStore->addColumn('version', 'integer', ['unsigned' => true]);
        $snapshotStore->addColumn('state', 'text');
        $snapshotStore->setPrimaryKey(['id']);
        $snapshotStore->addUniqueIndex(['aggregate_id']);

        // Account balances projection
        $balances = $schema->createTable('account_balances');
        $balances->addColumn('account_id', 'string', ['length' => 36]);
        $balances->addColumn('holder_name', 'string', ['length' => 255]);
        $balances->addColumn('currency', 'string', ['length' => 3]);
        $balances->addColumn('balance', 'bigint', ['default' => 0]);
        $balances->setPrimaryKey(['account_id']);

        // Account transactions projection
        $transactions = $schema->createTable('account_transactions');
        $transactions->addColumn('id', 'integer', ['autoincrement' => true]);
        $transactions->addColumn('account_id', 'string', ['length' => 36]);
        $transactions->addColumn('type', 'string', ['length' => 50]);
        $transactions->addColumn('amount', 'bigint');
        $transactions->addColumn('description', 'string', ['length' => 255, 'default' => '']);
        $transactions->addColumn('related_account_id', 'string', ['length' => 36, 'notnull' => false]);
        $transactions->addColumn('occurred_at', 'string', ['length' => 32]);
        $transactions->setPrimaryKey(['id']);
        $transactions->addIndex(['account_id']);

        $platform = $this->connection->getDatabasePlatform();

        foreach ($schema->toSql($platform) as $sql) {
            $this->connection->executeStatement($sql);
        }
    }
}
