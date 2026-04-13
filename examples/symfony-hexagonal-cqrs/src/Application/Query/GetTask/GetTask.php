<?php

declare(strict_types=1);

namespace App\Application\Query\GetTask;

use SolidFrame\Cqrs\Query;

final readonly class GetTask implements Query
{
    public function __construct(public string $taskId)
    {
    }
}
