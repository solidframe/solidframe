<?php

declare(strict_types=1);

namespace App\Infrastructure\Projection;

use App\Domain\Account\Event\AccountOpened;
use App\Domain\Account\Event\MoneyDeposited;
use App\Domain\Account\Event\MoneyWithdrawn;
use App\Domain\Account\Event\TransferReceived;
use App\Domain\Account\Event\TransferSent;
use Doctrine\DBAL\Connection;

final class AccountBalanceProjection
{
    public function __construct(private readonly Connection $connection) {}

    public function onAccountOpened(AccountOpened $event): void
    {
        $this->connection->insert('account_balances', [
            'account_id' => $event->accountId,
            'holder_name' => $event->holderName,
            'currency' => $event->currency,
            'balance' => $event->initialBalance,
        ]);
    }

    public function onMoneyDeposited(MoneyDeposited $event): void
    {
        $this->connection->executeStatement(
            'UPDATE account_balances SET balance = balance + ? WHERE account_id = ?',
            [$event->amount, $event->accountId],
        );
    }

    public function onMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        $this->connection->executeStatement(
            'UPDATE account_balances SET balance = balance - ? WHERE account_id = ?',
            [$event->amount, $event->accountId],
        );
    }

    public function onTransferSent(TransferSent $event): void
    {
        $this->connection->executeStatement(
            'UPDATE account_balances SET balance = balance - ? WHERE account_id = ?',
            [$event->amount, $event->accountId],
        );
    }

    public function onTransferReceived(TransferReceived $event): void
    {
        $this->connection->executeStatement(
            'UPDATE account_balances SET balance = balance + ? WHERE account_id = ?',
            [$event->amount, $event->accountId],
        );
    }

    public function reset(): void
    {
        $this->connection->executeStatement('DELETE FROM account_balances');
    }
}
