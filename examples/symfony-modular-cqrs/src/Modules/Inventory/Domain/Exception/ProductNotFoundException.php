<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Exception;

use SolidFrame\Core\Exception\SolidFrameException;

final class ProductNotFoundException extends \RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self("Product not found: {$id}");
    }
}
