<?php

namespace App\Http\Controllers;

use Cloudstudio\Ollama\Facades\Ollama;
use Inertia\Inertia;

class OllamaController extends Controller
{
    public function index()
    {
        $response = Ollama::models()->get();
        \Log::debug(json_encode($response));
        return Inertia::render('Dashboard');
    }
}
