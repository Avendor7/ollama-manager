<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use ArdaGnsrn\Ollama\Ollama;
class StatusController extends Controller
{
    public function index()
    {
        $client = Ollama::client(config('app.ollama_api_endpoint'));
        $response = $client->models()->runningList();


        return Inertia::render('Status',['running' => $response->toArray()]);
    }
}
