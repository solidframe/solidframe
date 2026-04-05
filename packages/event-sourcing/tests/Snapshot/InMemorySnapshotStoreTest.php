<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Tests\Snapshot;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Snapshot\InMemorySnapshotStore;
use SolidFrame\EventSourcing\Snapshot\Snapshot;

final class InMemorySnapshotStoreTest extends TestCase
{
    #[Test]
    public function savesAndLoadsSnapshot(): void
    {
        $store = new InMemorySnapshotStore();
        $snapshot = new Snapshot(
            aggregateId: 'agg-1',
            aggregateType: 'Order',
            version: 5,
            state: ['balance' => 100],
        );

        $store->save($snapshot);

        $loaded = $store->load($this->createIdentity('agg-1'));
        self::assertNotNull($loaded);
        self::assertSame('agg-1', $loaded->aggregateId);
        self::assertSame('Order', $loaded->aggregateType);
        self::assertSame(5, $loaded->version);
        self::assertSame(['balance' => 100], $loaded->state);
    }

    #[Test]
    public function returnsNullForUnknownAggregate(): void
    {
        $store = new InMemorySnapshotStore();

        self::assertNull($store->load($this->createIdentity('unknown')));
    }

    #[Test]
    public function overwritesExistingSnapshot(): void
    {
        $store = new InMemorySnapshotStore();

        $store->save(new Snapshot('agg-1', 'Order', 5, ['balance' => 100]));
        $store->save(new Snapshot('agg-1', 'Order', 10, ['balance' => 200]));

        $loaded = $store->load($this->createIdentity('agg-1'));
        self::assertNotNull($loaded);
        self::assertSame(10, $loaded->version);
        self::assertSame(['balance' => 200], $loaded->state);
    }

    private function createIdentity(string $value): IdentityInterface
    {
        return new class ($value) implements IdentityInterface {
            public function __construct(private readonly string $value) {}

            public function value(): string
            {
                return $this->value;
            }

            public function equals(IdentityInterface $other): bool
            {
                return $this->value === $other->value();
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }
}
