<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TaskController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::get('/dashboard/tasks', [TaskController::class, 'index'])
        ->name('dashboard.tasks.index');
    Route::post('/dashboard/tasks', [TaskController::class, 'store'])
        ->name('dashboard.tasks.store');
    Route::get('/dashboard/tasks/stats', [TaskController::class, 'stats'])
        ->name('dashboard.tasks.stats');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
