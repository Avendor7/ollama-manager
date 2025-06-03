<?php

namespace App\Http\Controllers;

use Generator;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Prism\Prism\Prism;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OllamaController extends Controller
{
    public function index()
    {
        //$client = Ollama::client();
        //$response = $client->models()->list();
        //Log::debug(json_encode($response));
        return Inertia::render('Dashboard');
    }

    public function handleChatStream(Request $request): StreamedResponse
    {
        
        return response()->stream(function () use ($request): Generator {
            $stream = Prism::text()
                ->using('ollama', 'llama3.1:8b')
                ->withMessages([

                ])
                ->withPrompt($request->input("prompt"))
                ->asStream();

            foreach ($stream as $response) {
                yield $response->text;
            }
        }, status: 200, headers: [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

}

