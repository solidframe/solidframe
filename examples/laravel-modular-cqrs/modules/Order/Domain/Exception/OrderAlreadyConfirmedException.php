<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Exception;

use SolidFrame\Core\Exception\SolidFrameException;

final class OrderAlreadyConfirmedException extends \RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self("Order already confirmed: {$id}");
    }
}
