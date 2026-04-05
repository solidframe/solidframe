<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Tests\Saga;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Saga\Saga\AbstractSaga;
use SolidFrame\Saga\State\SagaStatus;

final class AbstractSagaTest extends TestCase
{
    #[Test]
    public function generatesIdAutomatically(): void
    {
        $saga = new TestOrderSaga();

        self::assertNotEmpty($saga->id());
    }

    #[Test]
    public function acceptsCustomId(): void
    {
        $saga = new TestOrderSaga('custom-id');

        self::assertSame('custom-id', $saga->id());
    }

    #[Test]
    public function startsInProgress(): void
    {
        $saga = new TestOrderSaga();

        self::assertSame(SagaStatus::InProgress, $saga->status());
        self::assertFalse($saga->isCompleted());
        self::assertFalse($saga->isFailed());
    }

    #[Test]
    public function completesSuccessfully(): void
    {
        $saga = new TestOrderSaga();
        $saga->doComplete();

        self::assertSame(SagaStatus::Completed, $saga->status());
        self::assertTrue($saga->isCompleted());
    }

    #[Test]
    public function failsAndCompensates(): void
    {
        $compensated = [];
        $saga = new TestOrderSaga();
        $saga->addTestCompensation(function () use (&$compensated): void {
            $compensated[] = 'step1';
        });
        $saga->addTestCompensation(function () use (&$compensated): void {
            $compensated[] = 'step2';
        });

        $saga->doFail();

        self::assertSame(SagaStatus::Failed, $saga->status());
        self::assertTrue($saga->isFailed());
        self::assertSame(['step2', 'step1'], $compensated);
    }

    #[Test]
    public function compensatesInReverseOrder(): void
    {
        $order = [];
        $saga = new TestOrderSaga();
        $saga->addTestCompensation(function () use (&$order): void {
            $order[] = 'first';
        });
        $saga->addTestCompensation(function () use (&$order): void {
            $order[] = 'second';
        });
        $saga->addTestCompensation(function () use (&$order): void {
            $order[] = 'third';
        });

        $saga->compensate();

        self::assertSame(['third', 'second', 'first'], $order);
    }

    #[Test]
    public function clearsCompensationsAfterExecution(): void
    {
        $count = 0;
        $saga = new TestOrderSaga();
        $saga->addTestCompensation(function () use (&$count): void {
            $count++;
        });

        $saga->compensate();
        $saga->compensate();

        self::assertSame(1, $count);
    }

    #[Test]
    public function managesAssociations(): void
    {
        $saga = new TestOrderSaga();
        $saga->doAssociate('orderId', 'order-123');

        $associations = $saga->associations();
        self::assertCount(1, $associations);
        self::assertSame('orderId', $associations[0]->key);
        self::assertSame('order-123', $associations[0]->value);
    }

    #[Test]
    public function preventsDuplicateAssociations(): void
    {
        $saga = new TestOrderSaga();
        $saga->doAssociate('orderId', 'order-123');
        $saga->doAssociate('orderId', 'order-123');

        self::assertCount(1, $saga->associations());
    }

    #[Test]
    public function removesAssociation(): void
    {
        $saga = new TestOrderSaga();
        $saga->doAssociate('orderId', 'order-123');
        $saga->doAssociate('paymentId', 'pay-456');
        $saga->doRemoveAssociation('orderId');

        $associations = $saga->associations();
        self::assertCount(1, $associations);
        self::assertSame('paymentId', $associations[0]->key);
    }

    #[Test]
    public function supportsMultipleAssociations(): void
    {
        $saga = new TestOrderSaga();
        $saga->doAssociate('orderId', 'order-123');
        $saga->doAssociate('paymentId', 'pay-456');

        self::assertCount(2, $saga->associations());
    }
}

final class TestOrderSaga extends AbstractSaga
{
    public function doComplete(): void
    {
        $this->complete();
    }

    public function doFail(): void
    {
        $this->fail();
    }

    public function doAssociate(string $key, string $value): void
    {
        $this->associateWith($key, $value);
    }

    public function doRemoveAssociation(string $key): void
    {
        $this->removeAssociation($key);
    }

    public function addTestCompensation(callable $compensation): void
    {
        $this->addCompensation($compensation);
    }
}
