<?php

declare(strict_types=1);

namespace App\Application\Command;

use SolidFrame\Cqrs\Command;

final readonly class DepositMoney implements Command
{
    public function __construct(
        public string $accountId,
        public int $amount,
        public string $description = '',
    ) {}
}
