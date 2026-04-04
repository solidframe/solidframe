<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\ValueObject;

use Stringable;

interface ValueObjectInterface extends Stringable
{
    public function equals(self $other): bool;
}
