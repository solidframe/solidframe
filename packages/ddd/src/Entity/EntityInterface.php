<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Entity;

use SolidFrame\Core\Identity\IdentityInterface;

interface EntityInterface
{
    public function identity(): IdentityInterface;

    public function equals(self $other): bool;
}
