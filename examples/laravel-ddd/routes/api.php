<?php

declare(strict_types=1);

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::apiResource('books', BookController::class);
Route::post('books/{id}/borrow', [BookController::class, 'borrow']);
Route::post('books/{id}/return', [BookController::class, 'return']);
