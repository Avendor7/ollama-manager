<?php

namespace App\Http\Controllers;

use ArdaGnsrn\Ollama\Ollama;
use Inertia\Inertia;

class OllamaController extends Controller
{
    public function index()
    {
        /*
        $client = Ollama::client();

        $response = $client->models()->list();

        \Log::debug(json_encode($response));
        */
        return Inertia::render('Dashboard');
    }
}
