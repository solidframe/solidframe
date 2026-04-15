<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function opensAccount(): void
    {
        $response = $this->postJson('/api/accounts', [
            'holder_name' => 'Kadir Posul',
            'currency' => 'TRY',
            'initial_balance' => 10000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.holder_name', 'Kadir Posul')
            ->assertJsonPath('data.currency', 'TRY')
            ->assertJsonPath('data.balance', 10000);
    }

    #[Test]
    public function opensAccountWithZeroBalance(): void
    {
        $response = $this->postJson('/api/accounts', [
            'holder_name' => 'Ali Veli',
            'currency' => 'USD',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.balance', 0);
    }

    #[Test]
    public function validatesOpenAccountRequest(): void
    {
        $response = $this->postJson('/api/accounts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['holder_name', 'currency']);
    }

    #[Test]
    public function validatesInvalidCurrency(): void
    {
        $response = $this->postJson('/api/accounts', [
            'holder_name' => 'Test',
            'currency' => 'GBP',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['currency']);
    }

    #[Test]
    public function showsAccount(): void
    {
        $accountId = $this->createAccount();

        $response = $this->getJson("/api/accounts/{$accountId}");

        $response->assertOk()
            ->assertJsonPath('data.id', $accountId)
            ->assertJsonPath('data.holder_name', 'Kadir Posul');
    }

    #[Test]
    public function listsAccounts(): void
    {
        $this->createAccount('Kadir Posul');
        $this->createAccount('Ali Veli');

        $response = $this->getJson('/api/accounts');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function depositsMoney(): void
    {
        $accountId = $this->createAccount(initialBalance: 5000);

        $response = $this->postJson("/api/accounts/{$accountId}/deposit", [
            'amount' => 3000,
            'description' => 'Salary',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.balance', 8000);
    }

    #[Test]
    public function validatesDepositAmount(): void
    {
        $accountId = $this->createAccount();

        $response = $this->postJson("/api/accounts/{$accountId}/deposit", [
            'amount' => 0,
        ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function withdrawsMoney(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);

        $response = $this->postJson("/api/accounts/{$accountId}/withdraw", [
            'amount' => 3000,
            'description' => 'ATM',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.balance', 7000);
    }

    #[Test]
    public function rejectsWithdrawalExceedingBalance(): void
    {
        $accountId = $this->createAccount(initialBalance: 1000);

        $response = $this->postJson("/api/accounts/{$accountId}/withdraw", [
            'amount' => 5000,
        ]);

        $response->assertStatus(500);
    }

    #[Test]
    public function transfersMoney(): void
    {
        $sourceId = $this->createAccount('Kadir', initialBalance: 10000);
        $targetId = $this->createAccount('Ali', initialBalance: 5000);

        $response = $this->postJson("/api/accounts/{$sourceId}/transfer", [
            'target_account_id' => $targetId,
            'amount' => 3000,
            'description' => 'Rent',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.balance', 7000);

        $this->getJson("/api/accounts/{$targetId}")
            ->assertJsonPath('data.balance', 8000);
    }

    #[Test]
    public function rejectsSelfTransfer(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);

        $response = $this->postJson("/api/accounts/{$accountId}/transfer", [
            'target_account_id' => $accountId,
            'amount' => 1000,
        ]);

        $response->assertStatus(500);
    }

    #[Test]
    public function showsTransactionHistory(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);
        $this->postJson("/api/accounts/{$accountId}/deposit", ['amount' => 5000, 'description' => 'Bonus']);
        $this->postJson("/api/accounts/{$accountId}/withdraw", ['amount' => 2000, 'description' => 'ATM']);

        $response = $this->getJson("/api/accounts/{$accountId}/transactions");

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $types = array_column($response->json('data'), 'type');
        self::assertSame(['opening_deposit', 'deposit', 'withdrawal'], $types);
    }

    #[Test]
    public function showsBalanceAtDate(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);
        $this->postJson("/api/accounts/{$accountId}/deposit", ['amount' => 5000]);

        $response = $this->getJson("/api/accounts/{$accountId}/balance-at?date=" . date('Y-m-d'));

        $response->assertOk()
            ->assertJsonPath('data.balance', 15000)
            ->assertJsonPath('data.as_of', date('Y-m-d'));
    }

    #[Test]
    public function performsMultipleOperationsCorrectly(): void
    {
        $accountId = $this->createAccount(initialBalance: 50000);

        $this->postJson("/api/accounts/{$accountId}/deposit", ['amount' => 10000]);
        $this->postJson("/api/accounts/{$accountId}/withdraw", ['amount' => 5000]);
        $this->postJson("/api/accounts/{$accountId}/deposit", ['amount' => 20000]);
        $this->postJson("/api/accounts/{$accountId}/withdraw", ['amount' => 15000]);

        $this->getJson("/api/accounts/{$accountId}")
            ->assertJsonPath('data.balance', 60000);

        $this->getJson("/api/accounts/{$accountId}/transactions")
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function transferCreatesTransactionsOnBothSides(): void
    {
        $sourceId = $this->createAccount('Kadir', initialBalance: 10000);
        $targetId = $this->createAccount('Ali', initialBalance: 0);

        $this->postJson("/api/accounts/{$sourceId}/transfer", [
            'target_account_id' => $targetId,
            'amount' => 5000,
            'description' => 'Payment',
        ]);

        $sourceTransactions = $this->getJson("/api/accounts/{$sourceId}/transactions")->json('data');
        $targetTransactions = $this->getJson("/api/accounts/{$targetId}/transactions")->json('data');

        self::assertSame('transfer_sent', end($sourceTransactions)['type']);
        self::assertSame('transfer_received', end($targetTransactions)['type']);
    }

    private function createAccount(string $holderName = 'Kadir Posul', int $initialBalance = 0): string
    {
        $response = $this->postJson('/api/accounts', [
            'holder_name' => $holderName,
            'currency' => 'TRY',
            'initial_balance' => $initialBalance,
        ]);

        return $response->json('data.id');
    }
}
