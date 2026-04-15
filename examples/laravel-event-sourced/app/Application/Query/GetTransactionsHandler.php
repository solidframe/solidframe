<?php

declare(strict_types=1);

namespace App\Application\Query;

use Illuminate\Support\Facades\DB;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetTransactionsHandler implements QueryHandler
{
    /** @return list<array{type: string, amount: int, description: string, related_account_id: string|null, occurred_at: string}> */
    public function __invoke(GetTransactions $query): array
    {
        return array_values(
            DB::table('account_transactions')
                ->where('account_id', $query->accountId)
                ->orderBy('occurred_at')
                ->orderBy('id')
                ->get()
                ->map(fn (object $row): array => [
                    'type' => (string) $row->type,
                    'amount' => (int) $row->amount,
                    'description' => (string) $row->description,
                    'related_account_id' => $row->related_account_id !== null ? (string) $row->related_account_id : null,
                    'occurred_at' => (string) $row->occurred_at,
                ])
                ->all(),
        );
    }
}
