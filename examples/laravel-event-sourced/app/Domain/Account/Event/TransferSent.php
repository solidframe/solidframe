<?php

declare(strict_types=1);

namespace App\Domain\Account\Event;

use DateTimeImmutable;
use SolidFrame\Core\Event\DomainEventInterface;

final readonly class TransferSent implements DomainEventInterface
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $accountId,
        public string $targetAccountId,
        public int $amount,
        public string $description,
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }

    public function eventName(): string
    {
        return 'account.transfer_sent';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
