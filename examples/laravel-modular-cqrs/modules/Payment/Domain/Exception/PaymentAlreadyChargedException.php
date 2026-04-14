<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Exception;

use SolidFrame\Core\Exception\SolidFrameException;

final class PaymentAlreadyChargedException extends \RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self("Payment already charged: {$id}");
    }
}
