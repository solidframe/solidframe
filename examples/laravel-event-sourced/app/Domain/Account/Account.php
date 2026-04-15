<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Account\Event\AccountOpened;
use App\Domain\Account\Event\MoneyDeposited;
use App\Domain\Account\Event\MoneyWithdrawn;
use App\Domain\Account\Event\TransferReceived;
use App\Domain\Account\Event\TransferSent;
use App\Domain\Account\Exception\InsufficientBalanceException;
use App\Domain\Account\Exception\InvalidAmountException;
use App\Domain\Account\Exception\SelfTransferException;
use App\Domain\Account\ValueObject\AccountHolderName;
use App\Domain\Account\Currency;
use App\Domain\Account\ValueObject\Money;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\AbstractEventSourcedAggregateRoot;
use SolidFrame\EventSourcing\Snapshot\SnapshotableAggregateRootInterface;

final class Account extends AbstractEventSourcedAggregateRoot implements SnapshotableAggregateRootInterface
{
    private AccountHolderName $holderName;

    private Currency $currency;

    private Money $balance;

    public static function open(AccountId $id, AccountHolderName $holderName, Currency $currency, int $initialBalance = 0): self
    {
        if ($initialBalance < 0) {
            throw InvalidAmountException::notPositive($initialBalance);
        }

        $account = new self($id);
        $account->recordThat(new AccountOpened(
            accountId: $id->value(),
            holderName: $holderName->value,
            currency: $currency->value,
            initialBalance: $initialBalance,
        ));

        return $account;
    }

    public function deposit(int $amount, string $description = ''): void
    {
        if ($amount <= 0) {
            throw InvalidAmountException::notPositive($amount);
        }

        $this->recordThat(new MoneyDeposited(
            accountId: $this->identity()->value(),
            amount: $amount,
            description: $description,
        ));
    }

    public function withdraw(int $amount, string $description = ''): void
    {
        if ($amount <= 0) {
            throw InvalidAmountException::notPositive($amount);
        }

        $requested = Money::of($amount, $this->currency);

        if (!$this->balance->isGreaterThanOrEqual($requested)) {
            throw InsufficientBalanceException::forWithdrawal(
                $this->identity()->value(),
                $amount,
                $this->balance->amount,
            );
        }

        $this->recordThat(new MoneyWithdrawn(
            accountId: $this->identity()->value(),
            amount: $amount,
            description: $description,
        ));
    }

    public function sendTransfer(AccountId $targetAccountId, int $amount, string $description = ''): void
    {
        if ($amount <= 0) {
            throw InvalidAmountException::notPositive($amount);
        }

        if ($this->identity()->equals($targetAccountId)) {
            throw SelfTransferException::forAccount($this->identity()->value());
        }

        $requested = Money::of($amount, $this->currency);

        if (!$this->balance->isGreaterThanOrEqual($requested)) {
            throw InsufficientBalanceException::forTransfer(
                $this->identity()->value(),
                $amount,
                $this->balance->amount,
            );
        }

        $this->recordThat(new TransferSent(
            accountId: $this->identity()->value(),
            targetAccountId: $targetAccountId->value(),
            amount: $amount,
            description: $description,
        ));
    }

    public function receiveTransfer(AccountId $sourceAccountId, int $amount, string $description = ''): void
    {
        $this->recordThat(new TransferReceived(
            accountId: $this->identity()->value(),
            sourceAccountId: $sourceAccountId->value(),
            amount: $amount,
            description: $description,
        ));
    }

    public function holderName(): AccountHolderName
    {
        return $this->holderName;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    // -- Snapshot support --

    public function createSnapshotState(): mixed
    {
        return [
            'holder_name' => $this->holderName->value,
            'currency' => $this->currency->value,
            'balance' => $this->balance->amount,
        ];
    }

    /** @param iterable<DomainEventInterface> $remainingEvents */
    public static function reconstituteFromSnapshot(
        IdentityInterface $id,
        int $version,
        mixed $state,
        iterable $remainingEvents,
    ): static
    {
        /** @var array{holder_name: string, currency: string, balance: int} $state */
        $account = new self($id);
        $account->holderName = AccountHolderName::from($state['holder_name']);
        $account->currency = Currency::from($state['currency']);
        $account->balance = Money::of($state['balance'], $account->currency);
        $account->aggregateRootVersion = $version;

        foreach ($remainingEvents as $event) {
            $account->applyEvent($event);
        }

        return $account;
    }

    // -- Event apply methods --

    protected function applyAccountOpened(AccountOpened $event): void
    {
        $this->holderName = AccountHolderName::from($event->holderName);
        $this->currency = Currency::from($event->currency);
        $this->balance = Money::of($event->initialBalance, $this->currency);
    }

    protected function applyMoneyDeposited(MoneyDeposited $event): void
    {
        $this->balance = $this->balance->add(Money::of($event->amount, $this->currency));
    }

    protected function applyMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        $this->balance = $this->balance->subtract(Money::of($event->amount, $this->currency));
    }

    protected function applyTransferSent(TransferSent $event): void
    {
        $this->balance = $this->balance->subtract(Money::of($event->amount, $this->currency));
    }

    protected function applyTransferReceived(TransferReceived $event): void
    {
        $this->balance = $this->balance->add(Money::of($event->amount, $this->currency));
    }
}
