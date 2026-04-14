<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Exception;

use SolidFrame\Core\Exception\SolidFrameException;

final class OrderNotFoundException extends \RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self("Order not found: {$id}");
    }
}
