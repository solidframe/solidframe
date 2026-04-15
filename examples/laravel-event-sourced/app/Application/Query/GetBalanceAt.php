<?php

declare(strict_types=1);

namespace App\Application\Query;

use SolidFrame\Cqrs\Query;

final readonly class GetBalanceAt implements Query
{
    public function __construct(
        public string $accountId,
        public string $date,
    ) {}
}
