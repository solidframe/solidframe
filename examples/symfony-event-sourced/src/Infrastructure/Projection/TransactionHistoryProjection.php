<?php

declare(strict_types=1);

namespace App\Infrastructure\Projection;

use App\Domain\Account\Event\AccountOpened;
use App\Domain\Account\Event\MoneyDeposited;
use App\Domain\Account\Event\MoneyWithdrawn;
use App\Domain\Account\Event\TransferReceived;
use App\Domain\Account\Event\TransferSent;
use Doctrine\DBAL\Connection;

final class TransactionHistoryProjection
{
    public function __construct(private readonly Connection $connection) {}

    public function onAccountOpened(AccountOpened $event): void
    {
        if ($event->initialBalance <= 0) {
            return;
        }

        $this->connection->insert('account_transactions', [
            'account_id' => $event->accountId,
            'type' => 'opening_deposit',
            'amount' => $event->initialBalance,
            'description' => 'Opening deposit',
            'related_account_id' => null,
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function onMoneyDeposited(MoneyDeposited $event): void
    {
        $this->connection->insert('account_transactions', [
            'account_id' => $event->accountId,
            'type' => 'deposit',
            'amount' => $event->amount,
            'description' => $event->description,
            'related_account_id' => null,
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function onMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        $this->connection->insert('account_transactions', [
            'account_id' => $event->accountId,
            'type' => 'withdrawal',
            'amount' => $event->amount,
            'description' => $event->description,
            'related_account_id' => null,
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function onTransferSent(TransferSent $event): void
    {
        $this->connection->insert('account_transactions', [
            'account_id' => $event->accountId,
            'type' => 'transfer_sent',
            'amount' => $event->amount,
            'description' => $event->description,
            'related_account_id' => $event->targetAccountId,
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function onTransferReceived(TransferReceived $event): void
    {
        $this->connection->insert('account_transactions', [
            'account_id' => $event->accountId,
            'type' => 'transfer_received',
            'amount' => $event->amount,
            'description' => $event->description,
            'related_account_id' => $event->sourceAccountId,
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function reset(): void
    {
        $this->connection->executeStatement('DELETE FROM account_transactions');
    }
}
