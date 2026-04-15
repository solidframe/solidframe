<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Command\DepositMoney;
use App\Application\Command\OpenAccount;
use App\Application\Command\TransferMoney;
use App\Application\Command\WithdrawMoney;
use App\Application\Query\GetAccount;
use App\Application\Query\GetBalanceAt;
use App\Application\Query\GetTransactions;
use App\Application\Query\ListAccounts;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\OpenAccountRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;

final readonly class AccountController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {}

    public function index(): JsonResponse
    {
        $accounts = $this->queryBus->ask(new ListAccounts());

        return new JsonResponse(['data' => $accounts]);
    }

    public function store(OpenAccountRequest $request): JsonResponse
    {
        $accountId = Str::uuid()->toString();

        $this->commandBus->dispatch(new OpenAccount(
            accountId: $accountId,
            holderName: $request->validated('holder_name'),
            currency: $request->validated('currency'),
            initialBalance: (int) $request->validated('initial_balance', 0),
        ));

        $account = $this->queryBus->ask(new GetAccount($accountId));

        return new JsonResponse(['data' => $account], 201);
    }

    public function show(string $id): JsonResponse
    {
        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    public function deposit(string $id, DepositRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new DepositMoney(
            accountId: $id,
            amount: (int) $request->validated('amount'),
            description: $request->validated('description', ''),
        ));

        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    public function withdraw(string $id, WithdrawRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new WithdrawMoney(
            accountId: $id,
            amount: (int) $request->validated('amount'),
            description: $request->validated('description', ''),
        ));

        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    public function transfer(string $id, TransferRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new TransferMoney(
            sourceAccountId: $id,
            targetAccountId: $request->validated('target_account_id'),
            amount: (int) $request->validated('amount'),
            description: $request->validated('description', ''),
        ));

        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    public function transactions(string $id): JsonResponse
    {
        $transactions = $this->queryBus->ask(new GetTransactions($id));

        return new JsonResponse(['data' => $transactions]);
    }

    public function balanceAt(string $id, Request $request): JsonResponse
    {
        /** @var string $date */
        $date = $request->query('date', date('Y-m-d'));

        $result = $this->queryBus->ask(new GetBalanceAt(
            accountId: $id,
            date: $date,
        ));

        return new JsonResponse(['data' => $result]);
    }
}
