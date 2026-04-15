<?php

declare(strict_types=1);

namespace App\Infrastructure\Projection;

use App\Domain\Account\Event\AccountOpened;
use App\Domain\Account\Event\MoneyDeposited;
use App\Domain\Account\Event\MoneyWithdrawn;
use App\Domain\Account\Event\TransferReceived;
use App\Domain\Account\Event\TransferSent;
use Illuminate\Support\Facades\DB;

final class AccountBalanceProjection
{
    public function onAccountOpened(AccountOpened $event): void
    {
        DB::table('account_balances')->insert([
            'account_id' => $event->accountId,
            'holder_name' => $event->holderName,
            'currency' => $event->currency,
            'balance' => $event->initialBalance,
        ]);
    }

    public function onMoneyDeposited(MoneyDeposited $event): void
    {
        DB::table('account_balances')
            ->where('account_id', $event->accountId)
            ->increment('balance', $event->amount);
    }

    public function onMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        DB::table('account_balances')
            ->where('account_id', $event->accountId)
            ->decrement('balance', $event->amount);
    }

    public function onTransferSent(TransferSent $event): void
    {
        DB::table('account_balances')
            ->where('account_id', $event->accountId)
            ->decrement('balance', $event->amount);
    }

    public function onTransferReceived(TransferReceived $event): void
    {
        DB::table('account_balances')
            ->where('account_id', $event->accountId)
            ->increment('balance', $event->amount);
    }

    public function reset(): void
    {
        DB::table('account_balances')->truncate();
    }
}
