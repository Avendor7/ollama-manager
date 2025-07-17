<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ModelManagementController;
use Prism\Prism\Prism;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

//Route::get('dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

//Route::get('/dashboard', [OllamaController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [OllamaController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
    Route::delete('/dashboard/{chat}', [OllamaController::class, 'destroy'])->name('dashboard.destroy');
});


Route::post('/dashboard/new-chat', [OllamaController::class, 'createNewChat'])->middleware(['auth', 'verified'])->name('dashboard.new-chat');

Route::get('/status', [StatusController::class, 'index'])->name('status');

Route::get('/model-management', [ModelManagementController::class, 'index'])->name('model-management');

Route::post('/load-model', [OllamaController::class, 'loadModel'])->middleware(['auth', 'verified'])->name('load-model');
Route::post('/unload-model', [OllamaController::class, 'unloadModel'])->middleware(['auth', 'verified'])->name('unload-model');
Route::get('/ollama/running-model', [OllamaController::class, 'getRunningModel']);


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
