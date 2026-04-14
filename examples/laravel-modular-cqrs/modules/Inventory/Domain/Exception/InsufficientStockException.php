<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Exception;

use SolidFrame\Core\Exception\SolidFrameException;

final class InsufficientStockException extends \RuntimeException implements SolidFrameException
{
    public static function forProduct(string $productId, int $requested, int $available): self
    {
        return new self("Insufficient stock for product {$productId}: requested {$requested}, available {$available}");
    }
}
