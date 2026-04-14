<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;

Route::prefix('api')->group(function (): void {
    Route::get('payments/{orderId}', [PaymentController::class, 'show']);
});
