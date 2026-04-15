<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Account\Account;
use App\Domain\Account\AccountId;
use App\Domain\Account\Exception\AccountNotFoundException;
use App\Domain\Account\Port\AccountRepository;
use App\Infrastructure\Projection\AccountBalanceProjection;
use App\Infrastructure\Projection\TransactionHistoryProjection;
use ReflectionClass;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\EventSourcing\Exception\AggregateNotFoundException;
use SolidFrame\EventSourcing\Snapshot\SnapshotAggregateRootRepository;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final readonly class EventSourcedAccountRepository implements AccountRepository
{
    public function __construct(
        private SnapshotAggregateRootRepository $repository,
        private EventStoreInterface $eventStore,
        private AccountBalanceProjection $balanceProjection,
        private TransactionHistoryProjection $transactionProjection,
    ) {}

    public function load(AccountId $id): Account
    {
        try {
            /** @var Account */
            return $this->repository->load($id);
        } catch (AggregateNotFoundException) {
            throw AccountNotFoundException::forId($id->value());
        }
    }

    public function save(Account $account): void
    {
        $events = $account->releaseEvents();

        if ($events === []) {
            return;
        }

        $currentVersion = $account->aggregateRootVersion();
        $expectedVersion = $currentVersion - count($events);

        $this->eventStore->persist($account->identity(), $expectedVersion, $events);

        foreach ($events as $event) {
            $this->applyToProjections($event);
        }
    }

    private function applyToProjections(DomainEventInterface $event): void
    {
        $shortName = (new ReflectionClass($event))->getShortName();
        $method = 'on' . $shortName;

        if (method_exists($this->balanceProjection, $method)) {
            $this->balanceProjection->$method($event);
        }

        if (method_exists($this->transactionProjection, $method)) {
            $this->transactionProjection->$method($event);
        }
    }
}
