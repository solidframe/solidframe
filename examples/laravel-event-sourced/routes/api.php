<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('accounts', [AccountController::class, 'index']);
Route::post('accounts', [AccountController::class, 'store']);
Route::get('accounts/{id}', [AccountController::class, 'show']);
Route::post('accounts/{id}/deposit', [AccountController::class, 'deposit']);
Route::post('accounts/{id}/withdraw', [AccountController::class, 'withdraw']);
Route::post('accounts/{id}/transfer', [AccountController::class, 'transfer']);
Route::get('accounts/{id}/transactions', [AccountController::class, 'transactions']);
Route::get('accounts/{id}/balance-at', [AccountController::class, 'balanceAt']);
