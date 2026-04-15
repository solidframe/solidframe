<?php

declare(strict_types=1);

namespace App\Application\Query;

use Doctrine\DBAL\Connection;
use SolidFrame\Cqrs\QueryHandler;

final readonly class ListAccountsHandler implements QueryHandler
{
    public function __construct(
        private Connection $connection,
    ) {}

    /** @return list<array{id: string, holder_name: string, currency: string, balance: int}> */
    public function __invoke(ListAccounts $query): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM account_balances ORDER BY holder_name',
        );

        return array_map(static fn (array $row): array => [
            'id' => (string) $row['account_id'],
            'holder_name' => (string) $row['holder_name'],
            'currency' => (string) $row['currency'],
            'balance' => (int) $row['balance'],
        ], $rows);
    }
}
