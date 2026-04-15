<?php

declare(strict_types=1);

namespace App\Application\Command;

use SolidFrame\Cqrs\Command;

final readonly class OpenAccount implements Command
{
    public function __construct(
        public string $accountId,
        public string $holderName,
        public string $currency,
        public int $initialBalance = 0,
    ) {}
}
