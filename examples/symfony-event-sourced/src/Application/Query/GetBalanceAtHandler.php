<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Account\Account;
use App\Domain\Account\AccountId;
use App\Domain\Account\Exception\AccountNotFoundException;
use DateTimeImmutable;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Cqrs\QueryHandler;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final readonly class GetBalanceAtHandler implements QueryHandler
{
    public function __construct(
        private EventStoreInterface $eventStore,
    ) {}

    /** @return array{id: string, balance: int, currency: string, as_of: string} */
    public function __invoke(GetBalanceAt $query): array
    {
        $accountId = new AccountId($query->accountId);
        $cutoff = new DateTimeImmutable($query->date . ' 23:59:59.999999');

        $allEvents = $this->eventStore->load($accountId);

        if ($allEvents === []) {
            throw AccountNotFoundException::forId($query->accountId);
        }

        $eventsUpToDate = array_filter(
            $allEvents,
            static fn (DomainEventInterface $event): bool => $event->occurredAt() <= $cutoff,
        );

        if ($eventsUpToDate === []) {
            throw AccountNotFoundException::forId($query->accountId);
        }

        $account = Account::reconstituteFromEvents($accountId, $eventsUpToDate);

        return [
            'id' => $query->accountId,
            'balance' => $account->balance()->amount,
            'currency' => $account->currency()->value,
            'as_of' => $query->date,
        ];
    }
}
