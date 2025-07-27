<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ModelManagementController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ChatController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
    Route::delete('/dashboard/{chat}', [ChatController::class, 'destroy'])->name('dashboard.destroy');
    Route::post('/dashboard/new-chat', [ChatController::class, 'createNewChat'])->middleware(['auth', 'verified'])->name('dashboard.new-chat');

    Route::get('/status', [StatusController::class, 'index'])->name('status');

    Route::get('/model-management', [ModelManagementController::class, 'index'])->name('model-management');
    Route::post('/load-model', [ModelManagementController::class, 'loadModel'])->middleware(['auth', 'verified'])->name('load-model');
    Route::post('/unload-model', [ModelManagementController::class, 'unloadModel'])->middleware(['auth', 'verified'])->name('unload-model');
    Route::get('/ollama/running-model', [ModelManagementController::class, 'getRunningModel']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
