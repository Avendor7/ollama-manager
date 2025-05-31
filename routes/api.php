<?php

use App\Http\Controllers\AIStreamController;
use App\Http\Controllers\OllamaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/ollama/chat', [OllamaController::class, 'chat'])->middleware(['auth', 'verified']);
Route::post('/prism/chat', [OllamaController::class, 'prismChat'])->middleware(['auth', 'verified']);
Route::post('/ai/stream-compose', [AIStreamController::class, 'streamCompose']);
