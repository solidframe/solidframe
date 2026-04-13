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

        $projects = $schema->createTable('projects');
        $projects->addColumn('id', 'string', ['length' => 36]);
        $projects->addColumn('name', 'string', ['length' => 100]);
        $projects->addColumn('description', 'text', ['notnull' => false]);
        $projects->addColumn('status', 'string', ['length' => 20, 'default' => 'active']);
        $projects->addColumn('created_at', 'datetime_immutable');
        $projects->addColumn('updated_at', 'datetime_immutable');
        $projects->setPrimaryKey(['id']);
        $projects->addIndex(['status']);

        $tasks = $schema->createTable('tasks');
        $tasks->addColumn('id', 'string', ['length' => 36]);
        $tasks->addColumn('project_id', 'string', ['length' => 36]);
        $tasks->addColumn('title', 'string', ['length' => 255]);
        $tasks->addColumn('description', 'text', ['notnull' => false]);
        $tasks->addColumn('status', 'string', ['length' => 20, 'default' => 'open']);
        $tasks->addColumn('priority', 'string', ['length' => 20, 'default' => 'medium']);
        $tasks->addColumn('assignee', 'string', ['length' => 255, 'notnull' => false]);
        $tasks->addColumn('created_at', 'datetime_immutable');
        $tasks->addColumn('updated_at', 'datetime_immutable');
        $tasks->setPrimaryKey(['id']);
        $tasks->addForeignKeyConstraint('projects', ['project_id'], ['id'], ['onDelete' => 'CASCADE']);
        $tasks->addIndex(['project_id']);
        $tasks->addIndex(['status']);
        $tasks->addIndex(['assignee']);

        $platform = $this->connection->getDatabasePlatform();

        foreach ($schema->toSql($platform) as $sql) {
            $this->connection->executeStatement($sql);
        }
    }
}
