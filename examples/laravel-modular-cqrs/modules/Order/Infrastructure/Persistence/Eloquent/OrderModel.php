<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $customer_email
 * @property int $total_amount
 * @property string $status
 * @property-read Collection<int, OrderItemModel> $items
 */
final class OrderModel extends Model
{
    use HasUuids;

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'customer_email',
        'total_amount',
        'status',
    ];

    /** @return HasMany<OrderItemModel, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
