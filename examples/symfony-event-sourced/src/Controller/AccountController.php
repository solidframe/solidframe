<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Command\DepositMoney;
use App\Application\Command\OpenAccount;
use App\Application\Command\TransferMoney;
use App\Application\Command\WithdrawMoney;
use App\Application\Query\GetAccount;
use App\Application\Query\GetBalanceAt;
use App\Application\Query\GetTransactions;
use App\Application\Query\ListAccounts;
use App\Domain\Account\AccountId;
use App\Http\RequestValidator;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/accounts')]
final readonly class AccountController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private RequestValidator $requestValidator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $accounts = $this->queryBus->ask(new ListAccounts());

        return new JsonResponse(['data' => $accounts]);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'holder_name' => [new Assert\NotBlank(), new Assert\Length(min: 2, max: 255)],
            'currency' => [new Assert\NotBlank(), new Assert\Choice(choices: ['TRY', 'USD', 'EUR'])],
            'initial_balance' => new Assert\Optional([new Assert\Type('integer'), new Assert\GreaterThanOrEqual(0)]),
        ]));

        $accountId = AccountId::generate()->value();

        $this->commandBus->dispatch(new OpenAccount(
            accountId: $accountId,
            holderName: $data['holder_name'],
            currency: $data['currency'],
            initialBalance: (int) ($data['initial_balance'] ?? 0),
        ));

        $account = $this->queryBus->ask(new GetAccount($accountId));

        return new JsonResponse(['data' => $account], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    #[Route('/{id}/deposit', methods: ['POST'])]
    public function deposit(Request $request, string $id): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'amount' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\GreaterThanOrEqual(1)],
            'description' => new Assert\Optional([new Assert\Type('string'), new Assert\Length(max: 255)]),
        ]));

        $this->commandBus->dispatch(new DepositMoney(
            accountId: $id,
            amount: (int) $data['amount'],
            description: $data['description'] ?? '',
        ));

        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    #[Route('/{id}/withdraw', methods: ['POST'])]
    public function withdraw(Request $request, string $id): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'amount' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\GreaterThanOrEqual(1)],
            'description' => new Assert\Optional([new Assert\Type('string'), new Assert\Length(max: 255)]),
        ]));

        $this->commandBus->dispatch(new WithdrawMoney(
            accountId: $id,
            amount: (int) $data['amount'],
            description: $data['description'] ?? '',
        ));

        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    #[Route('/{id}/transfer', methods: ['POST'])]
    public function transfer(Request $request, string $id): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'target_account_id' => [new Assert\NotBlank(), new Assert\Uuid()],
            'amount' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\GreaterThanOrEqual(1)],
            'description' => new Assert\Optional([new Assert\Type('string'), new Assert\Length(max: 255)]),
        ]));

        $this->commandBus->dispatch(new TransferMoney(
            sourceAccountId: $id,
            targetAccountId: $data['target_account_id'],
            amount: (int) $data['amount'],
            description: $data['description'] ?? '',
        ));

        $account = $this->queryBus->ask(new GetAccount($id));

        return new JsonResponse(['data' => $account]);
    }

    #[Route('/{id}/transactions', methods: ['GET'])]
    public function transactions(string $id): JsonResponse
    {
        $transactions = $this->queryBus->ask(new GetTransactions($id));

        return new JsonResponse(['data' => $transactions]);
    }

    #[Route('/{id}/balance-at', methods: ['GET'])]
    public function balanceAt(Request $request, string $id): JsonResponse
    {
        /** @var string $date */
        $date = $request->query->getString('date') ?: date('Y-m-d');

        $result = $this->queryBus->ask(new GetBalanceAt(
            accountId: $id,
            date: $date,
        ));

        return new JsonResponse(['data' => $result]);
    }
}
