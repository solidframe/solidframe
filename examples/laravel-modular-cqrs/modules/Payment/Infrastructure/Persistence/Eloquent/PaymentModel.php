<?php

declare(strict_types=1);

namespace Modules\Payment\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $order_id
 * @property int $amount
 * @property string $method
 * @property string $status
 */
final class PaymentModel extends Model
{
    use HasUuids;

    protected $table = 'payments';

    protected $fillable = [
        'id',
        'order_id',
        'amount',
        'method',
        'status',
    ];
}
