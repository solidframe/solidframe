<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $order_id
 * @property string $product_id
 * @property int $quantity
 * @property int $unit_price
 */
final class OrderItemModel extends Model
{
    use HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'id',
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    public $timestamps = false;
}
