<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Account\AccountId;
use App\Domain\Account\Port\AccountRepository;
use SolidFrame\Cqrs\CommandHandler;

final readonly class TransferMoneyHandler implements CommandHandler
{
    public function __construct(
        private AccountRepository $accounts,
    ) {}

    public function __invoke(TransferMoney $command): void
    {
        $sourceId = new AccountId($command->sourceAccountId);
        $targetId = new AccountId($command->targetAccountId);

        $source = $this->accounts->load($sourceId);
        $source->sendTransfer($targetId, $command->amount, $command->description);
        $this->accounts->save($source);

        $target = $this->accounts->load($targetId);
        $target->receiveTransfer($sourceId, $command->amount, $command->description);
        $this->accounts->save($target);
    }
}
