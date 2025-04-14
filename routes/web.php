<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\StatusController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

//Route::get('dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [OllamaController::class, 'index'])->name('dashboard');

Route::get('/status', [StatusController::class, 'index'])->name('status');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
