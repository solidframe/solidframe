<?php

declare(strict_types=1);

namespace App\Application\Command;

use SolidFrame\Cqrs\Command;

final readonly class TransferMoney implements Command
{
    public function __construct(
        public string $sourceAccountId,
        public string $targetAccountId,
        public int $amount,
        public string $description = '',
    ) {}
}
