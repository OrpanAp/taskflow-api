<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\AiTaskProposalController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::get('/dashboard/tasks', [TaskController::class, 'index'])
        ->name('dashboard.tasks.index');
    Route::post('/dashboard/tasks', [TaskController::class, 'store'])
        ->name('dashboard.tasks.store');
    Route::put('/dashboard/tasks/{task}', [TaskController::class, 'update'])
        ->whereNumber('task')
        ->name('dashboard.tasks.update');
    Route::delete('/dashboard/tasks/{task}', [TaskController::class, 'destroy'])
        ->whereNumber('task')
        ->name('dashboard.tasks.destroy');
    Route::get('/dashboard/tasks/stats', [TaskController::class, 'stats'])
        ->name('dashboard.tasks.stats');

    Route::post(
        '/dashboard/ai/task-proposals',
        [AiTaskProposalController::class, 'store']
    )
        ->middleware('throttle:10,1')
        ->name('dashboard.ai.task-proposals.store');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
