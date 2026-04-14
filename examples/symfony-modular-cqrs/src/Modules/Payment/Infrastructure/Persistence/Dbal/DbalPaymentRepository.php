<?php

declare(strict_types=1);

namespace App\Modules\Payment\Infrastructure\Persistence\Dbal;

use App\Modules\Payment\Domain\Exception\PaymentNotFoundException;
use App\Modules\Payment\Domain\Payment;
use App\Modules\Payment\Domain\PaymentId;
use App\Modules\Payment\Domain\PaymentStatus;
use App\Modules\Payment\Domain\Port\PaymentRepository;
use Doctrine\DBAL\Connection;

final readonly class DbalPaymentRepository implements PaymentRepository
{
    public function __construct(private Connection $connection) {}

    public function find(PaymentId $id): Payment
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM payments WHERE id = ?',
            [$id->value()],
        );

        if ($row === false) {
            throw PaymentNotFoundException::forId($id->value());
        }

        return $this->toDomain($row);
    }

    public function findByOrderId(string $orderId): Payment
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM payments WHERE order_id = ?',
            [$orderId],
        );

        if ($row === false) {
            throw PaymentNotFoundException::forOrderId($orderId);
        }

        return $this->toDomain($row);
    }

    public function save(Payment $payment): void
    {
        $now = new \DateTimeImmutable();

        $existing = $this->connection->fetchOne(
            'SELECT id FROM payments WHERE id = ?',
            [$payment->identity()->value()],
        );

        if ($existing !== false) {
            $this->connection->update('payments', [
                'order_id' => $payment->orderId(),
                'amount' => $payment->amount(),
                'method' => $payment->method(),
                'status' => $payment->status()->value,
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ], ['id' => $payment->identity()->value()]);
        } else {
            $this->connection->insert('payments', [
                'id' => $payment->identity()->value(),
                'order_id' => $payment->orderId(),
                'amount' => $payment->amount(),
                'method' => $payment->method(),
                'status' => $payment->status()->value,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toDomain(array $row): Payment
    {
        return Payment::reconstitute(
            id: new PaymentId((string) $row['id']),
            orderId: (string) $row['order_id'],
            amount: (int) $row['amount'],
            method: (string) $row['method'],
            status: PaymentStatus::from((string) $row['status']),
        );
    }
}
