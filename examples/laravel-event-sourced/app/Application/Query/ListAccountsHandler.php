<?php

declare(strict_types=1);

namespace App\Application\Query;

use Illuminate\Support\Facades\DB;
use SolidFrame\Cqrs\QueryHandler;

final readonly class ListAccountsHandler implements QueryHandler
{
    /** @return list<array{id: string, holder_name: string, currency: string, balance: int}> */
    public function __invoke(ListAccounts $query): array
    {
        return array_values(
            DB::table('account_balances')
                ->orderBy('holder_name')
                ->get()
                ->map(fn (object $row): array => [
                    'id' => (string) $row->account_id,
                    'holder_name' => (string) $row->holder_name,
                    'currency' => (string) $row->currency,
                    'balance' => (int) $row->balance,
                ])
                ->all(),
        );
    }
}
