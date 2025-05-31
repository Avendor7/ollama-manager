<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use ArdaGnsrn\Ollama\Ollama;

class OllamaController extends Controller
{
    public function index()
    {
        //$client = Ollama::client();
        //$response = $client->models()->list();
        //Log::debug(json_encode($response));
        return Inertia::render('Dashboard');
    }

}

