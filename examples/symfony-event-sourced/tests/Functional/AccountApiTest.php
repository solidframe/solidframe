<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AccountApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        $tables = $connection->createSchemaManager()->listTableNames();
        if (in_array('account_transactions', $tables, true)) {
            $connection->executeStatement('DELETE FROM account_transactions');
            $connection->executeStatement('DELETE FROM account_balances');
            $connection->executeStatement('DELETE FROM event_store');
            $connection->executeStatement('DELETE FROM snapshot_store');
        } else {
            (new SchemaManager($connection))->createSchema();
        }
    }

    #[Test]
    public function opensAccount(): void
    {
        $this->request('POST', '/api/accounts', [
            'holder_name' => 'Kadir Posul',
            'currency' => 'TRY',
            'initial_balance' => 10000,
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('Kadir Posul', $data['holder_name']);
        self::assertSame('TRY', $data['currency']);
        self::assertSame(10000, $data['balance']);
    }

    #[Test]
    public function opensAccountWithZeroBalance(): void
    {
        $this->request('POST', '/api/accounts', [
            'holder_name' => 'Ali Veli',
            'currency' => 'USD',
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame(0, $data['balance']);
    }

    #[Test]
    public function validatesOpenAccountRequest(): void
    {
        $this->request('POST', '/api/accounts', []);

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function validatesInvalidCurrency(): void
    {
        $this->request('POST', '/api/accounts', [
            'holder_name' => 'Test',
            'currency' => 'GBP',
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function showsAccount(): void
    {
        $accountId = $this->createAccount();

        $this->client->request('GET', "/api/accounts/{$accountId}");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame($accountId, $data['id']);
        self::assertSame('Kadir Posul', $data['holder_name']);
    }

    #[Test]
    public function listsAccounts(): void
    {
        $this->createAccount('Kadir Posul');
        $this->createAccount('Ali Veli');

        $this->client->request('GET', '/api/accounts');

        self::assertResponseStatusCodeSame(200);

        $json = $this->responseJson();
        self::assertCount(2, $json['data']);
    }

    #[Test]
    public function depositsMoney(): void
    {
        $accountId = $this->createAccount(initialBalance: 5000);

        $this->request('POST', "/api/accounts/{$accountId}/deposit", [
            'amount' => 3000,
            'description' => 'Salary',
        ]);

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame(8000, $data['balance']);
    }

    #[Test]
    public function validatesDepositAmount(): void
    {
        $accountId = $this->createAccount();

        $this->request('POST', "/api/accounts/{$accountId}/deposit", [
            'amount' => 0,
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function withdrawsMoney(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);

        $this->request('POST', "/api/accounts/{$accountId}/withdraw", [
            'amount' => 3000,
            'description' => 'ATM',
        ]);

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame(7000, $data['balance']);
    }

    #[Test]
    public function rejectsWithdrawalExceedingBalance(): void
    {
        $accountId = $this->createAccount(initialBalance: 1000);

        $this->request('POST', "/api/accounts/{$accountId}/withdraw", [
            'amount' => 5000,
        ]);

        self::assertResponseStatusCodeSame(409);
    }

    #[Test]
    public function transfersMoney(): void
    {
        $sourceId = $this->createAccount('Kadir', initialBalance: 10000);
        $targetId = $this->createAccount('Ali', initialBalance: 5000);

        $this->request('POST', "/api/accounts/{$sourceId}/transfer", [
            'target_account_id' => $targetId,
            'amount' => 3000,
            'description' => 'Rent',
        ]);

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame(7000, $data['balance']);

        $this->client->request('GET', "/api/accounts/{$targetId}");
        $targetData = $this->responseData();
        self::assertSame(8000, $targetData['balance']);
    }

    #[Test]
    public function rejectsSelfTransfer(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);

        $this->request('POST', "/api/accounts/{$accountId}/transfer", [
            'target_account_id' => $accountId,
            'amount' => 1000,
        ]);

        self::assertResponseStatusCodeSame(409);
    }

    #[Test]
    public function showsTransactionHistory(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);
        $this->request('POST', "/api/accounts/{$accountId}/deposit", ['amount' => 5000, 'description' => 'Bonus']);
        $this->request('POST', "/api/accounts/{$accountId}/withdraw", ['amount' => 2000, 'description' => 'ATM']);

        $this->client->request('GET', "/api/accounts/{$accountId}/transactions");

        self::assertResponseStatusCodeSame(200);

        $json = $this->responseJson();
        self::assertCount(3, $json['data']);

        $types = array_column($json['data'], 'type');
        self::assertSame(['opening_deposit', 'deposit', 'withdrawal'], $types);
    }

    #[Test]
    public function showsBalanceAtDate(): void
    {
        $accountId = $this->createAccount(initialBalance: 10000);
        $this->request('POST', "/api/accounts/{$accountId}/deposit", ['amount' => 5000]);

        $this->client->request('GET', "/api/accounts/{$accountId}/balance-at?date=" . date('Y-m-d'));

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame(15000, $data['balance']);
        self::assertSame(date('Y-m-d'), $data['as_of']);
    }

    #[Test]
    public function performsMultipleOperationsCorrectly(): void
    {
        $accountId = $this->createAccount(initialBalance: 50000);

        $this->request('POST', "/api/accounts/{$accountId}/deposit", ['amount' => 10000]);
        $this->request('POST', "/api/accounts/{$accountId}/withdraw", ['amount' => 5000]);
        $this->request('POST', "/api/accounts/{$accountId}/deposit", ['amount' => 20000]);
        $this->request('POST', "/api/accounts/{$accountId}/withdraw", ['amount' => 15000]);

        $this->client->request('GET', "/api/accounts/{$accountId}");
        $data = $this->responseData();
        self::assertSame(60000, $data['balance']);

        $this->client->request('GET', "/api/accounts/{$accountId}/transactions");
        $json = $this->responseJson();
        self::assertCount(5, $json['data']);
    }

    #[Test]
    public function transferCreatesTransactionsOnBothSides(): void
    {
        $sourceId = $this->createAccount('Kadir', initialBalance: 10000);
        $targetId = $this->createAccount('Ali', initialBalance: 0);

        $this->request('POST', "/api/accounts/{$sourceId}/transfer", [
            'target_account_id' => $targetId,
            'amount' => 5000,
            'description' => 'Payment',
        ]);

        $this->client->request('GET', "/api/accounts/{$sourceId}/transactions");
        $sourceTransactions = $this->responseJson()['data'];

        $this->client->request('GET', "/api/accounts/{$targetId}/transactions");
        $targetTransactions = $this->responseJson()['data'];

        self::assertSame('transfer_sent', end($sourceTransactions)['type']);
        self::assertSame('transfer_received', end($targetTransactions)['type']);
    }

    private function createAccount(string $holderName = 'Kadir Posul', int $initialBalance = 0): string
    {
        $this->request('POST', '/api/accounts', [
            'holder_name' => $holderName,
            'currency' => 'TRY',
            'initial_balance' => $initialBalance,
        ]);

        return $this->responseData()['id'];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function request(string $method, string $uri, array $data = []): void
    {
        $this->client->request($method, $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
    }

    /** @return array<string, mixed> */
    private function responseData(): array
    {
        return $this->responseJson()['data'];
    }

    /** @return array<string, mixed> */
    private function responseJson(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
