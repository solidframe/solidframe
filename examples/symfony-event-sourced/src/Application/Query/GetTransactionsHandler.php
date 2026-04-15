<?php

declare(strict_types=1);

namespace App\Application\Query;

use Doctrine\DBAL\Connection;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetTransactionsHandler implements QueryHandler
{
    public function __construct(
        private Connection $connection,
    ) {}

    /** @return list<array{type: string, amount: int, description: string, related_account_id: string|null, occurred_at: string}> */
    public function __invoke(GetTransactions $query): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM account_transactions WHERE account_id = ? ORDER BY occurred_at, id',
            [$query->accountId],
        );

        return array_map(static fn (array $row): array => [
            'type' => (string) $row['type'],
            'amount' => (int) $row['amount'],
            'description' => (string) $row['description'],
            'related_account_id' => $row['related_account_id'] !== null ? (string) $row['related_account_id'] : null,
            'occurred_at' => (string) $row['occurred_at'],
        ], $rows);
    }
}
