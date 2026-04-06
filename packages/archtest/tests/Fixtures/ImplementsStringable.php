<?php

declare(strict_types=1);

namespace SolidFrame\Archtest\Tests\Fixtures;

use Stringable;

final class ImplementsStringable implements Stringable
{
    public function __toString(): string
    {
        return '';
    }
}
