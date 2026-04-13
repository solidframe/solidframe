<?php

declare(strict_types=1);

namespace App\Application\Query\GetProject;

use SolidFrame\Cqrs\Query;

final readonly class GetProject implements Query
{
    public function __construct(public string $projectId)
    {
    }
}
