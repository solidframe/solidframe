<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Account\Exception\AccountNotFoundException;
use Illuminate\Support\Facades\DB;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetAccountHandler implements QueryHandler
{
    /** @return array{id: string, holder_name: string, currency: string, balance: int} */
    public function __invoke(GetAccount $query): array
    {
        $row = DB::table('account_balances')->where('account_id', $query->accountId)->first();

        if ($row === null) {
            throw AccountNotFoundException::forId($query->accountId);
        }

        return [
            'id' => $row->account_id,
            'holder_name' => $row->holder_name,
            'currency' => $row->currency,
            'balance' => (int) $row->balance,
        ];
    }
}
