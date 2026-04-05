<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Store;

use SolidFrame\Saga\Saga\SagaInterface;
use SolidFrame\Saga\State\Association;

interface SagaStoreInterface
{
    public function find(string $id): ?SagaInterface;

    /** @param class-string<SagaInterface> $sagaClass */
    public function findByAssociation(string $sagaClass, Association $association): ?SagaInterface;

    public function save(SagaInterface $saga): void;

    public function delete(string $id): void;
}
