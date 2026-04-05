<?php

declare(strict_types=1);

namespace SolidFrame\Saga\State;

enum SagaStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Failed = 'failed';
}
