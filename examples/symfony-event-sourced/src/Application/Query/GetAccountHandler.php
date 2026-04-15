<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Account\Exception\AccountNotFoundException;
use Doctrine\DBAL\Connection;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetAccountHandler implements QueryHandler
{
    public function __construct(
        private Connection $connection,
    ) {}

    /** @return array{id: string, holder_name: string, currency: string, balance: int} */
    public function __invoke(GetAccount $query): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM account_balances WHERE account_id = ?',
            [$query->accountId],
        );

        if ($row === false) {
            throw AccountNotFoundException::forId($query->accountId);
        }

        return [
            'id' => (string) $row['account_id'],
            'holder_name' => (string) $row['holder_name'],
            'currency' => (string) $row['currency'],
            'balance' => (int) $row['balance'],
        ];
    }
}
