<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Tests\Store;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Saga\Saga\AbstractSaga;
use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\Store\InMemorySagaStore;

final class InMemorySagaStoreTest extends TestCase
{
    #[Test]
    public function savesAndFindsById(): void
    {
        $store = new InMemorySagaStore();
        $saga = new StoreTestSaga('saga-1');

        $store->save($saga);

        self::assertSame($saga, $store->find('saga-1'));
    }

    #[Test]
    public function returnsNullForUnknownId(): void
    {
        $store = new InMemorySagaStore();

        self::assertNull($store->find('unknown'));
    }

    #[Test]
    public function deletesSaga(): void
    {
        $store = new InMemorySagaStore();
        $saga = new StoreTestSaga('saga-1');

        $store->save($saga);
        $store->delete('saga-1');

        self::assertNull($store->find('saga-1'));
    }

    #[Test]
    public function findsByAssociation(): void
    {
        $store = new InMemorySagaStore();
        $saga = new StoreTestSaga('saga-1');
        $saga->doAssociate('orderId', 'order-123');

        $store->save($saga);

        $found = $store->findByAssociation(
            StoreTestSaga::class,
            new Association('orderId', 'order-123'),
        );

        self::assertSame($saga, $found);
    }

    #[Test]
    public function returnsNullWhenAssociationNotFound(): void
    {
        $store = new InMemorySagaStore();

        self::assertNull($store->findByAssociation(
            StoreTestSaga::class,
            new Association('orderId', 'unknown'),
        ));
    }

    #[Test]
    public function filtersByClassWhenFindingByAssociation(): void
    {
        $store = new InMemorySagaStore();
        $saga = new StoreTestSaga('saga-1');
        $saga->doAssociate('orderId', 'order-123');
        $store->save($saga);

        $found = $store->findByAssociation(
            AnotherTestSaga::class,
            new Association('orderId', 'order-123'),
        );

        self::assertNull($found);
    }

    #[Test]
    public function overwritesExistingSaga(): void
    {
        $store = new InMemorySagaStore();
        $saga = new StoreTestSaga('saga-1');
        $store->save($saga);

        $updated = new StoreTestSaga('saga-1');
        $updated->doAssociate('orderId', 'order-999');
        $store->save($updated);

        self::assertSame($updated, $store->find('saga-1'));
    }
}

final class StoreTestSaga extends AbstractSaga
{
    public function doAssociate(string $key, string $value): void
    {
        $this->associateWith($key, $value);
    }
}

final class AnotherTestSaga extends AbstractSaga {}
