<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Account\AccountId;
use App\Domain\Account\Port\AccountRepository;
use SolidFrame\Cqrs\CommandHandler;

final readonly class DepositMoneyHandler implements CommandHandler
{
    public function __construct(
        private AccountRepository $accounts,
    ) {}

    public function __invoke(DepositMoney $command): void
    {
        $account = $this->accounts->load(new AccountId($command->accountId));
        $account->deposit($command->amount, $command->description);
        $this->accounts->save($account);
    }
}
