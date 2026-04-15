<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\Projection\AccountBalanceProjection;
use App\Infrastructure\Projection\TransactionHistoryProjection;
use Doctrine\DBAL\Connection;
use ReflectionClass;
use SolidFrame\Core\Event\DomainEventInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:projection:rebuild', description: 'Rebuild all projections by replaying events from the event store')]
final class RebuildProjectionsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AccountBalanceProjection $balanceProjection,
        private readonly TransactionHistoryProjection $transactionProjection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Resetting projections...');

        $this->balanceProjection->reset();
        $this->transactionProjection->reset();

        $io->info('Replaying events...');

        $rows = $this->connection->fetchAllAssociative('SELECT * FROM event_store ORDER BY id');
        $count = 0;

        foreach ($rows as $row) {
            /** @var class-string<DomainEventInterface> $eventClass */
            $eventClass = $row['event_type'];
            $payload = json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR);

            $reflection = new ReflectionClass($eventClass);
            $event = $reflection->newInstanceWithoutConstructor();

            foreach ($payload as $property => $value) {
                if (!$reflection->hasProperty($property)) {
                    continue;
                }

                $prop = $reflection->getProperty($property);
                $type = $prop->getType();

                if ($type instanceof \ReflectionNamedType && $type->getName() === \DateTimeImmutable::class && is_string($value)) {
                    $value = new \DateTimeImmutable($value);
                }

                $prop->setValue($event, $value);
            }

            $shortName = $reflection->getShortName();
            $method = 'on' . $shortName;

            if (method_exists($this->balanceProjection, $method)) {
                $this->balanceProjection->$method($event);
            }

            if (method_exists($this->transactionProjection, $method)) {
                $this->transactionProjection->$method($event);
            }

            $count++;
        }

        $io->success(sprintf('Rebuilt projections from %d events.', $count));

        return Command::SUCCESS;
    }
}
