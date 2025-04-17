<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use ArdaGnsrn\Ollama\Ollama;
class StatusController extends Controller
{
    public function index()
    {
        $client = Ollama::client('http://192.168.5.220:11434');
        $response = $client->models()->runningList();


        return Inertia::render('Status',['running' => $response->toArray()]);
    }
}
