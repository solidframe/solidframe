<?php

declare(strict_types=1);

namespace App\Application\Query;

use SolidFrame\Cqrs\Query;

final readonly class GetTransactions implements Query
{
    public function __construct(
        public string $accountId,
    ) {}
}
