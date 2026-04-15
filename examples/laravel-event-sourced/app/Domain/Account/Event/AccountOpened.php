<?php

declare(strict_types=1);

namespace App\Domain\Account\Event;

use DateTimeImmutable;
use SolidFrame\Core\Event\DomainEventInterface;

final readonly class AccountOpened implements DomainEventInterface
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $accountId,
        public string $holderName,
        public string $currency,
        public int $initialBalance,
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }

    public function eventName(): string
    {
        return 'account.opened';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
