<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Account\Account;
use App\Domain\Account\AccountId;
use App\Domain\Account\Currency;
use App\Domain\Account\Port\AccountRepository;
use App\Domain\Account\ValueObject\AccountHolderName;
use SolidFrame\Cqrs\CommandHandler;

final readonly class OpenAccountHandler implements CommandHandler
{
    public function __construct(
        private AccountRepository $accounts,
    ) {}

    public function __invoke(OpenAccount $command): void
    {
        $account = Account::open(
            id: new AccountId($command->accountId),
            holderName: AccountHolderName::from($command->holderName),
            currency: Currency::from($command->currency),
            initialBalance: $command->initialBalance,
        );

        $this->accounts->save($account);
    }
}
