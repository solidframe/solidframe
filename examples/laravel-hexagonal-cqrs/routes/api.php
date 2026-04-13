<?php

declare(strict_types=1);

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Projects
Route::get('projects', [ProjectController::class, 'index']);
Route::post('projects', [ProjectController::class, 'store']);
Route::get('projects/{id}', [ProjectController::class, 'show']);
Route::post('projects/{id}/archive', [ProjectController::class, 'archive']);

// Tasks
Route::get('tasks', [TaskController::class, 'index']);
Route::post('tasks', [TaskController::class, 'store']);
Route::get('tasks/{id}', [TaskController::class, 'show']);
Route::post('tasks/{id}/assign', [TaskController::class, 'assign']);
Route::post('tasks/{id}/complete', [TaskController::class, 'complete']);
Route::post('tasks/{id}/reopen', [TaskController::class, 'reopen']);
