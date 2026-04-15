<?php

declare(strict_types=1);

namespace App\Domain\Account\Event;

use DateTimeImmutable;
use SolidFrame\Core\Event\DomainEventInterface;

final readonly class TransferReceived implements DomainEventInterface
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $accountId,
        public string $sourceAccountId,
        public int $amount,
        public string $description,
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }

    public function eventName(): string
    {
        return 'account.transfer_received';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
