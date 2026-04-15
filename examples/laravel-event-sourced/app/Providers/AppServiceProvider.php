<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Account\Account;
use App\Domain\Account\Port\AccountRepository;
use App\Infrastructure\Persistence\EventSourcedAccountRepository;
use Illuminate\Support\ServiceProvider;
use SolidFrame\EventSourcing\Snapshot\SnapshotAggregateRootRepository;
use SolidFrame\EventSourcing\Snapshot\SnapshotStoreInterface;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SnapshotAggregateRootRepository::class, fn (): SnapshotAggregateRootRepository => new SnapshotAggregateRootRepository(
            aggregateClass: Account::class,
            eventStore: $this->app->make(EventStoreInterface::class),
            snapshotStore: $this->app->make(SnapshotStoreInterface::class),
        ));

        $this->app->bind(AccountRepository::class, EventSourcedAccountRepository::class);
    }
}
