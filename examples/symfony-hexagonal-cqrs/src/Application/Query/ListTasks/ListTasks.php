<?php

declare(strict_types=1);

namespace App\Application\Query\ListTasks;

use SolidFrame\Cqrs\Query;

final readonly class ListTasks implements Query
{
    public function __construct(
        public ?string $projectId = null,
        public ?string $status = null,
        public ?string $assignee = null,
    ) {
    }
}
