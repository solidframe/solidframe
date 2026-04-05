<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Saga;

use SolidFrame\Core\Identity\UuidIdentity;
use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\State\SagaStatus;

abstract class AbstractSaga implements SagaInterface
{
    private readonly string $id;

    private SagaStatus $status = SagaStatus::InProgress;

    /** @var list<Association> */
    private array $associations = [];

    /** @var list<callable> */
    private array $compensations = [];

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? UuidIdentity::generate()->value();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function status(): SagaStatus
    {
        return $this->status;
    }

    public function associations(): array
    {
        return $this->associations;
    }

    public function isCompleted(): bool
    {
        return $this->status === SagaStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === SagaStatus::Failed;
    }

    protected function associateWith(string $key, string $value): void
    {
        $association = new Association($key, $value);

        foreach ($this->associations as $existing) {
            if ($existing->equals($association)) {
                return;
            }
        }

        $this->associations[] = $association;
    }

    protected function removeAssociation(string $key): void
    {
        $this->associations = array_values(
            array_filter(
                $this->associations,
                fn(Association $a): bool => $a->key !== $key,
            ),
        );
    }

    protected function addCompensation(callable $compensation): void
    {
        $this->compensations[] = $compensation;
    }

    protected function complete(): void
    {
        $this->status = SagaStatus::Completed;
    }

    protected function fail(): void
    {
        $this->status = SagaStatus::Failed;
        $this->compensate();
    }

    public function compensate(): void
    {
        foreach (array_reverse($this->compensations) as $compensation) {
            $compensation();
        }

        $this->compensations = [];
    }
}
