<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Exception;

use SolidFrame\Core\Exception\SolidFrameException;

final class PaymentNotFoundException extends \RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self("Payment not found: {$id}");
    }

    public static function forOrderId(string $orderId): self
    {
        return new self("Payment not found for order: {$orderId}");
    }
}
