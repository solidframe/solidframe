<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Store;

use SolidFrame\Saga\Saga\SagaInterface;
use SolidFrame\Saga\State\Association;

final class InMemorySagaStore implements SagaStoreInterface
{
    /** @var array<string, SagaInterface> */
    private array $sagas = [];

    public function find(string $id): ?SagaInterface
    {
        return $this->sagas[$id] ?? null;
    }

    public function findByAssociation(string $sagaClass, Association $association): ?SagaInterface
    {
        foreach ($this->sagas as $saga) {
            if (!$saga instanceof $sagaClass) {
                continue;
            }

            foreach ($saga->associations() as $existing) {
                if ($existing->equals($association)) {
                    return $saga;
                }
            }
        }

        return null;
    }

    public function save(SagaInterface $saga): void
    {
        $this->sagas[$saga->id()] = $saga;
    }

    public function delete(string $id): void
    {
        unset($this->sagas[$id]);
    }
}
