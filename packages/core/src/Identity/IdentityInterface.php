<?php

declare(strict_types=1);

namespace SolidFrame\Core\Identity;

use Stringable;

interface IdentityInterface extends Stringable
{
    public function value(): string;

    public function equals(self $other): bool;
}
