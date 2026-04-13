<?php

declare(strict_types=1);

namespace App\Domain\Project;

enum ProjectStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
