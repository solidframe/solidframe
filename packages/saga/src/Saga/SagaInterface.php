<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Saga;

use SolidFrame\Saga\State\Association;
use SolidFrame\Saga\State\SagaStatus;

interface SagaInterface
{
    public function id(): string;

    public function status(): SagaStatus;

    /** @return list<Association> */
    public function associations(): array;

    public function isCompleted(): bool;

    public function isFailed(): bool;
}
