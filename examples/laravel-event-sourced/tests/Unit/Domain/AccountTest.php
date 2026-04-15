<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Account\Account;
use App\Domain\Account\AccountId;
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    private AccountId $accountId;

    protected function setUp(): void
    {
        $this->accountId = AccountId::generate();
    }

    #[Test]
    public function opensAccountWithZeroBalance(): void
    {
        $account = $this->openAccount();

        self::assertSame(0, $account->balance()->amount);
        self::assertSame(Currency::TRY, $account->currency());
        self::assertSame('Kadir Posul', $account->holderName()->value);
    }

    #[Test]
    public function opensAccountWithInitialBalance(): void
    {
        $account = $this->openAccount(10000);

        self::assertSame(10000, $account->balance()->amount);

        $events = $account->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(AccountOpened::class, $events[0]);
    }

    #[Test]
    public function rejectsNegativeInitialBalance(): void
    {
        $this->expectException(InvalidAmountException::class);

        Account::open(
            id: AccountId::generate(),
            holderName: AccountHolderName::from('Kadir Posul'),
            currency: Currency::TRY,
            initialBalance: -100,
        );
    }

    #[Test]
    public function depositsMoney(): void
    {
        $account = $this->openAccount();
        $account->releaseEvents();

        $account->deposit(5000, 'Salary');

        self::assertSame(5000, $account->balance()->amount);

        $events = $account->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(MoneyDeposited::class, $events[0]);
        self::assertSame(5000, $events[0]->amount);
        self::assertSame('Salary', $events[0]->description);
    }

    #[Test]
    public function rejectsZeroDeposit(): void
    {
        $account = $this->openAccount();

        $this->expectException(InvalidAmountException::class);
        $account->deposit(0);
    }

    #[Test]
    public function withdrawsMoney(): void
    {
        $account = $this->openAccount(10000);
        $account->releaseEvents();

        $account->withdraw(3000, 'ATM');

        self::assertSame(7000, $account->balance()->amount);

        $events = $account->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(MoneyWithdrawn::class, $events[0]);
    }

    #[Test]
    public function rejectsWithdrawalExceedingBalance(): void
    {
        $account = $this->openAccount(1000);

        $this->expectException(InsufficientBalanceException::class);
        $account->withdraw(2000);
    }

    #[Test]
    public function rejectsZeroWithdrawal(): void
    {
        $account = $this->openAccount(1000);

        $this->expectException(InvalidAmountException::class);
        $account->withdraw(0);
    }

    #[Test]
    public function sendsTransfer(): void
    {
        $account = $this->openAccount(10000);
        $account->releaseEvents();

        $targetId = AccountId::generate();
        $account->sendTransfer($targetId, 3000, 'Rent');

        self::assertSame(7000, $account->balance()->amount);

        $events = $account->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TransferSent::class, $events[0]);
        self::assertSame($targetId->value(), $events[0]->targetAccountId);
    }

    #[Test]
    public function rejectsTransferExceedingBalance(): void
    {
        $account = $this->openAccount(1000);

        $this->expectException(InsufficientBalanceException::class);
        $account->sendTransfer(AccountId::generate(), 5000);
    }

    #[Test]
    public function rejectsSelfTransfer(): void
    {
        $account = $this->openAccount(10000);

        $this->expectException(SelfTransferException::class);
        $account->sendTransfer($this->accountId, 1000);
    }

    #[Test]
    public function receivesTransfer(): void
    {
        $account = $this->openAccount(5000);
        $account->releaseEvents();

        $sourceId = AccountId::generate();
        $account->receiveTransfer($sourceId, 2000, 'Gift');

        self::assertSame(7000, $account->balance()->amount);

        $events = $account->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TransferReceived::class, $events[0]);
    }

    #[Test]
    public function reconstitutesFromEvents(): void
    {
        $account = $this->openAccount(10000);
        $account->deposit(5000, 'Bonus');
        $account->withdraw(2000, 'Shopping');

        $events = $account->releaseEvents();

        $reconstituted = Account::reconstituteFromEvents($this->accountId, $events);

        self::assertSame(13000, $reconstituted->balance()->amount);
        self::assertSame('Kadir Posul', $reconstituted->holderName()->value);
        self::assertSame(Currency::TRY, $reconstituted->currency());
        self::assertSame(3, $reconstituted->aggregateRootVersion());
    }

    #[Test]
    public function createsAndReconstitutesFromSnapshot(): void
    {
        $account = $this->openAccount(10000);
        $account->deposit(5000);
        $account->releaseEvents();

        $state = $account->createSnapshotState();
        self::assertIsArray($state);

        $account->withdraw(2000);
        $remainingEvents = $account->releaseEvents();

        $reconstituted = Account::reconstituteFromSnapshot(
            $this->accountId,
            2,
            $state,
            $remainingEvents,
        );

        self::assertSame(13000, $reconstituted->balance()->amount);
        self::assertSame(3, $reconstituted->aggregateRootVersion());
    }

    #[Test]
    public function tracksVersionCorrectly(): void
    {
        $account = $this->openAccount();
        self::assertSame(1, $account->aggregateRootVersion());

        $account->deposit(1000);
        self::assertSame(2, $account->aggregateRootVersion());

        $account->withdraw(500);
        self::assertSame(3, $account->aggregateRootVersion());
    }

    private function openAccount(int $initialBalance = 0): Account
    {
        return Account::open(
            id: $this->accountId,
            holderName: AccountHolderName::from('Kadir Posul'),
            currency: Currency::TRY,
            initialBalance: $initialBalance,
        );
    }
}
