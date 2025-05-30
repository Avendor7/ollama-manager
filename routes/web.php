<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ModelManagementController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

//Route::get('dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [OllamaController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/api/ollama/chat', [OllamaController::class, 'chat'])->middleware(['auth', 'verified']);

Route::get('/status', [StatusController::class, 'index'])->name('status');

Route::get('/model-management', [ModelManagementController::class, 'index'])->name('model-management');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
