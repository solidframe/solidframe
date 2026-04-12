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

        $table = $schema->createTable('books');
        $table->addColumn('id', 'string', ['length' => 36]);
        $table->addColumn('title', 'string', ['length' => 255]);
        $table->addColumn('author', 'string', ['length' => 255]);
        $table->addColumn('isbn', 'string', ['length' => 13]);
        $table->addColumn('status', 'string', ['length' => 20, 'default' => 'available']);
        $table->addColumn('borrower', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('updated_at', 'datetime_immutable');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['author']);
        $table->addIndex(['status']);
        $table->addUniqueIndex(['isbn']);

        $platform = $this->connection->getDatabasePlatform();

        foreach ($schema->toSql($platform) as $sql) {
            $this->connection->executeStatement($sql);
        }
    }
}
